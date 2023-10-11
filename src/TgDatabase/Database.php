<?php

namespace TgDatabase;

use TgLog\Log;

/**
 * Provides a better way to construct SQL from data pieces.
 */
class Database {

    /** The database config */
	protected $config;
	/** The database connection */
	public    $con;
	/** Warned against use of old interface */
	protected $deprecationWarning;

	/**
	 * Constructor.
	 * @param array $config - configuration array (see README.md)
	 * @param \TgUtils\Auth\CredentialsProvider $provider - provider for credentials from an external source (optional)
	 */
	public function __construct($config, \TgUtils\Auth\CredentialsProvider $provider = NULL) {
		$this->config = $config;
		$this->deprecationWarning = FALSE;
		$this->connect($provider);
	}

	/**
	 * Connects the database backend.
	 * @param \TgUtils\Auth\CredentialsProvider $provider - provider for credentials from an external source (optional)
	 */ 
	protected function connect(\TgUtils\Auth\CredentialsProvider $provider = NULL) {
		if ($this->con == null) {
			$username = '';
			$password = '';
			if ($provider != NULL) {
				$username = $provider->getUsername();
				$password = $provider->getPassword();
			} else {
				$username = $this->config['user'];
				$password = $this->config['pass'];
			}
			$this->con = new \mysqli(
				$this->config['host'],
				$username,
				$password,
				$this->config['dbname'],
				$this->config['port']
			);
			if ($this->con->connect_errno) {
				error_log('Failed to connect to MySQL: '.$this->con->connect_errno);
			} else {
		        $this->configureConnection();
			}
		}
	}

	/**
	 * Preconfigures a new connection with UTC timezone and UTF-8 character encoding.
	 */
	protected function configureConnection() {
		$this->con->query('SET time_zone = \'UTC\'');
		$this->con->set_charset("utf8");
	}

	/**
	 * Escapes strings for usage in SQL statements.
	 * <p><b>Attention!</b> Strings from external sources shall always be escaped before being
	 *    used in SQL.
	 * @param string $s - the string to be escaped
	 * @return string the escaped string
	 */
	public function escape($s) {
		if ($s == NULL) return NULL;
		return $this->con->real_escape_string($s);
	}

	/**
	 * Escapes and quotes strings in single quotes for usage in SQL statements.
	 * <p><b>Attention!</b> Strings from external sources shall always be quoted and escaped before being
	 *    used in SQL.</p>
	 * @param string $s - the string to be quoted
	 * @return string the quoted and escaped string
	 */
	public function quote($s) {
		return '\''.$this->escape($s).'\'';
	}

	/**
	 * Quote the identifer (e.g. a table or attribute name).
	 * If the identifier must be qualified by an alias then function takes two arguments.
	 * @param mixed $aliasOrIdentifier - a string containing alias or identifier or an array containing both
	 * @param mixed $identifier        - the identifier string for the alias or an array of alias and identifier.
	 * @return the quoted identifier 
	 */
	public function quoteName($aliasOrIdentifier, $identifier = NULL) {
		if ($identifier != NULL) {
			if (is_array($identifier)) {
				return $this->_quoteName($identifier[0]).'.'.$this->_quoteName($identifier[1]);
			} else if ($aliasOrIdentifier != NULL) {
			    return $this->_quoteName($aliasOrIdentifier).'.'.$this->_quoteName($identifier);
			}
			return $this->_quoteName($identifier);
		} else if (is_array($aliasOrIdentifier)) {
			return $this->_quoteName($aliasOrIdentifier[0]).'.'.$this->_quoteName($aliasOrIdentifier[1]);
		}
		return $this->_quoteName($aliasOrIdentifier);

	}

	/**
	 * Quotes a simple field or table name.
	 * <p>Field and table names shall always be quoted in backticks to avoid misinterpretation.</p>
	 * @param string $s - name to be quoted
	 * @return string the quoted string
	 */
	protected function _quoteName($s) {
		return '`'.$s.'`';
	}

	/**
	 * Checks existance of a table.
	 * @param string $tableName - name of table to be checked
	 * @return string TRUE when table exists, FALSE otherwise.
	 */
	public function tableExists($tableName) {
		$res = $this->query('SELECT * FROM '.$tableName);
		$rc  = $res !== FALSE;
		if ($res) $res->free();
		return $rc;
	}

	/**
	 * Describes a table.
	 * {
     *   "Field":   "uid",
     *   "Type":    "int(10) unsigned",
     *   "Null":    "NO",
     *   "Key":     "PRI",
     *   "Default": null,
     *   "Extra":   "auto_increment"
     * }
	 * @param string $tableName - name of table to be described
	 * @return array of columns (empty when error occured).
	 */
	public function describeTable($tableName) {
		$rc = array();
		foreach ($this->queryList('DESCRIBE '.$tableName) AS $field) {
			$rc[$field->Field] = $field;
		}
		return $rc;
	}

	/**
	 * Execute the given SQL.
	 * <p>This can be any arbitrary SQL statement. The function will only replace the table prefix
	 *    which can appear as placeholder #__.</p>
	 * @param string $sql - SQL statement
	 * @return mixed - \mysqli_result object or FALSE.
	 */
	public function query($sql) {
		$sql = $this->replaceTablePrefix($sql);
		return $this->con->query($sql);
	}

	/**
	  * Creates a new query object for this database.
	  * @param string $tableName  - the table to be queried
	  * @param string $modelClass - the result class in the query.
	  */
	public function createQuery($tableName, $modelClass = NULL, $alias = NULL) {
		return new Criterion\QueryImpl($this, $tableName, $modelClass, $alias);
	}

	/**
	  * Creates a new query object for this database.
	  * @param string $tableName  - the table to be queried
	  * @param string $modelClass - the result class in the query.
	  * @Deprecated Use #createQuery instead
	  */
	public function createCriteria($tableName, $modelClass = NULL, $alias = NULL) {
		$this->warnDeprecation();
		return new Criterion\QueryImpl($this, $tableName, $modelClass, $alias);
	}

	/**
	 * Internal function to log an error appearing with given SQL statement.
	 * <p>Will log the error text (from connection object), the SQL statement and the stacktrace.</p>
	 * @param string $sql - SQL statement that caused the problem.
	 */
	private function logError($sql) {
		Log::error($this->error());
		Log::error($sql);
		Log::errorStackTrace(__FILE__);
	}

	/**
	 * Performs the given SQL statement and expect a single row to return.
	 * <p>The first row in the result will be fetched as an object. Additional rows are ignored.</p>
	 * @param string $sql   - the SQL statement to be executed (table prefix will be replaced)
	 * @param string $class - the class name of the object to return.
	 * @return mixed the fetched object or FALSE.
	 */
	public function querySingle($sql, $class = NULL) {
		if ($class == NULL) $class = 'stdClass';

		$sql = $this->replaceTablePrefix($sql);
		$rc  = null;
		$res = $this->query($sql);
		if ($res !== FALSE) {
			$rc = $res->fetch_object($class);
			$res->free();
		} else {
			$this->logError($sql);
		}
		return $rc;
	}

	/**
	 * Performs the given SQL statement and returns all rows in a list.
	 * @param string $sql   - the SQL statement to be executed (table prefix will be replaced)
	 * @param string $class - the class name of the objects to return.
	 * @return mixed the array of fetched objects or FALSE.
	 */
	public function queryList($sql, $class = NULL) {
		if ($class == NULL) $class = 'stdClass';

		$sql = $this->replaceTablePrefix($sql);
		$rc  = array();
		$res = $this->query($sql);
		if ($res !== FALSE) {
			while (($obj = $res->fetch_object($class)) !== null) {
				$rc[] = $obj;
			}
			$res->free();
		} else {
			$this->logError($sql);
		}
		return $rc;
	}

	/**
	 * Returns the last ID of the row created (if any)
	 * @return int the last inserted ID
	 */
	public function insert_id() {
		return $this->con->insert_id;
	}

	/**
	 * Returns the error from the database connection.
	 * @return string error text
	 */
	public function error() {
	    if ($this->con->connect_error) {
	        return $this->con->connect_error;
	    }
		return $this->con->error;
	}

	/**
	 * Return TRUE when the database had a problem with the last task.
	 * @return boolean - TRUE when connection failed or last SQL command failed.
	 */
	public function hasError() {
	    return $this->con->connect_errno || $this->con->errno;
	}
	
	/**
	 * Inserts a new row into a table.
	 * <p>All fields (for objects) or keys (for arrays) are used as column names.</p>
	 * @param string $table - the table name (table prefix will be replaced)
	 * @param mixed $fields - array or object with fields and their values of new row.
	 * @return mixed ID of newly inserted row or FALSE in case of an error.
	 */
	public function insert($table, $fields) {
		$table = $this->replaceTablePrefix($table);
		if (is_object($fields)) $fields = get_object_vars($fields);
		$fieldNames = array_keys($fields);
		$sqlFieldNames = '`'.implode('`,`', $fieldNames).'`';
		$values = array();
		foreach ($fields AS $k => $v) {
			if ($v === NULL) $values[] = 'NULL';
			else $values[] = $this->prepareValue($v);
		}
		$sql = 'INSERT INTO '.$table.' ('.$sqlFieldNames.') VALUES ('.implode(',', $values).')';
		Log::debug($sql);
		$rc = $this->query($sql);
		if ($rc === FALSE) {
			$this->logError($sql);
		} else {
			$rc = $this->insert_id();
		}
		return $rc;
	}

	/**
	 * Updates rows in a table.
	 * <p>All fields (for objects) or keys (for arrays) are used for the update.</p>
	 * @param string $table - the table name (table prefix will be replaced)
	 * @param mixed $fields - array or object with fields and their values of the update.
	 * @param mixed $where - WHERE clause (without keyword) or restrictions array - shall not be empty!
	 * @return mixed list of updated rows or FALSE in case of an error.
	 */
	public function update($table, $fields, $where) {
		$query        = $this->createQuery($table);
		$restrictions = Restrictions::toRestrictions($where);
		if (Restrictions::$hasDeprecatedUse) $this->warnDeprecation();
		if ($restrictions != NULL) $query->add($restrictions);
		$rc = $query->save($fields);
		if ($rc === FALSE) {
			$this->logError($query->getUpdateSql($fields));
		} else {
			$rc = $query->list();
		}
		return $rc;
	}

	/**
	 * Deleted rows from a table.
	 * @param string $table - the table name (table prefix will be replaced)
	 * @param mixed $where - WHERE clause (without keyword) or restrictions array - MUST not be empty!
	 * @return boolean TRUE or FALSE in case of an error.
	 */
	public function delete($table, $where) {
		$query        = $this->createQuery($table);
		$restrictions = Restrictions::toRestrictions($where);
		if (Restrictions::$hasDeprecatedUse) $this->warnDeprecation();
		if ($restrictions != NULL) $query->add($restrictions);
		$rc = $query->delete();
		if ($rc === FALSE) {
			$this->logError($query->getDeleteSql());
		}
		return $rc;
	}

	/**
	 * Prepares a value for usage in a SQL statement.
	 * <p>This method automatically escapes and quotes strings as well takes care of
	 *    \TgUtils\Date objects.</p>
	 * @param mixed $value - value to be prepared
	 * @return string string that can be used in a SQL statement.
	 */
	public function prepareValue($value) {
		$rc = $value;
		if (is_object($value)) {
			if (get_class($value) == 'TgUtils\\Date') $rc = $this->quote($value->toMysql(true));
			else if (is_a($value, 'TgUtils\\SelfJsonEncoder')) $rc = $this->quote($value->json_encode());
			else $rc = $this->quote(json_encode($value));
		} else if (is_array($value)) {
			$rc = $this->quote(json_encode($value));
		} else if (is_numeric($value) && !is_string($value)) {
			// Nothing to do
		} else {
			$rc = $this->quote($value);
		}
		return $rc;
	}

	/**
	 * Updates a single row in a table.
	 * <p>All fields (for objects) or keys (for arrays) are used for the update.</p>
	 * <p><b>Attention</b> This method only assumes to update a single row as it returns the
	 *    first row only that was updated.</p>
	 * @param string $table - the table name (table prefix will be replaced)
	 * @param mixed $fields - array or object with fields and their values of the update.
	 * @param string $where - WHERE clause (without keyword) - shall not be empty!
	 * @return mixed updated row or FALSE in case of an error.
	 */
	public function updateSingle($table, $fields, $where) {
		$rc = $this->update($table, $fields, $where);
		if (is_array($rc) && count($rc) > 0) return $rc[0];
		return $rc;
	}

	/**
	 * Returns the next auto increment value for this class. (Use with care!)
	 * @return int - next auto increment value as UID.
	 */	
	public function getNextUid($tableName) {
		$sql = 'SELECT `auto_increment` FROM INFORMATION_SCHEMA.TABLES '.
			   'WHERE (table_schema='.$this->quote($this->replaceTablePrefix($this->config['dbname'])).') AND '.
			   '(table_name='.$this->quote($this->replaceTablePrefix($tableName)).')'; 
		$record = $this->querySingle($sql);
		if (is_object($record)) return $record->auto_increment;
		return 0;
	}

	/** 
	 * Replaces the #__ in a table name with the tablePrefix (if configured).
	 * @param string $s - the table name
	 * @param string the table name with prefix replaced
	 */
	public function replaceTablePrefix($s) {
		$prefix = $this->config['tablePrefix'];
		if (!$prefix) $prefix = '';
		return str_replace('#__', $prefix, $s);
	}

	/**
	 * Warns about deprecation. Once.
	 */
	protected function warnDeprecation() {
		if (!$this->deprecationWarning) {
			$this->deprecationWarning = TRUE;
			\TgLog\Log::warn('Deprecated SQL interface used in: '.get_class($this));
		}
	}

}
