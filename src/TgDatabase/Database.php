<?php

namespace TgDatabase;

use TgLog\Log;

/**
 * Provides a better way to construct SQL from data pieces.
 */
class Database {

    /** The database config */
	protected $dbconfig;
	/** The database connection */
	public    $con;

	/**
	 * Constructor.
	 * @param array $dbconfig - configuration array (see README.md)
	 * @param \TgUtils\Auth\CredentialsProvider $provider - provider for credentials from an external source (optional)
	 */
	public function __construct($dbconfig, \TgUtils\Auth\CredentialsProvider $provider = NULL) {
		$this->dbconfig = $dbconfig;
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
				$username = $this->dbconfig['user'];
				$password = $this->dbconfig['pass'];
			}
			$this->con = new \mysqli(
				$this->dbconfig['host'],
				$username,
				$password,
				$this->dbconfig['dbname'],
				$this->dbconfig['port']
			);
			if ($this->con->connect_errno) {
				error_log('Failed to connect to MySQL: '.$this->con->connect_errno);
			}
			$this->configureConnection();
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
	 * Quotes a field or table name.
	 * <p>Field and table names shall always be quoted in backticks to avoid misinterpretation.</p>
	 * @param string $s - name to be quoted
	 * @return string the quoted string
	 */
	public function quoteName($s) {
		return '`'.$s.'`';
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
	public function querySingle($sql, $class = 'stdClass') {
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
	public function queryList($sql, $class = 'stdClass') {
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
		return $this->con->error;
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
	 * @param string $where - WHERE clause (without keyword) - shall not be empty!
	 * @return mixed list of updated rows or FALSE in case of an error.
	 */
	public function update($table, $fields, $where) {
		$table = $this->replaceTablePrefix($table);
		if (is_object($fields)) $fields = get_object_vars($fields);
		$values = array();
		foreach ($fields AS $k => $v) {
			$value = $v;
			if ($v === NULL) $value = 'NULL';
			else $value = $this->prepareValue($v);
			$values[] = '`'.$k.'`='.$value;
		}
		if (($where != NULL) && (trim($where) != '')) $where = ' WHERE '.$where;
		$sql = 'UPDATE '.$table.' SET '.implode(', ', $values).$where;
		Log::debug($sql);
		$rc = $this->query($sql);
		if ($rc === FALSE) {
			$this->logError($sql);
		} else {
			$rc = $this->queryList('SELECT * FROM '.$table.$where);
		}
		return $rc;
	}

	/**
	 * Deleted rows from a table.
	 * @param string $table - the table name (table prefix will be replaced)
	 * @param string $where - WHERE clause (without keyword) - must not be empty!
	 * @return boolean TRUE or FALSE in case of an error.
	 */
	public function delete($table, $where) {
		$table = $this->replaceTablePrefix($table);
		$sql = 'DELETE FROM '.$table.' WHERE '.$where;
		Log::debug($sql);
		$rc = $this->query($sql);
		if ($rc === FALSE) {
			$this->logError($sql);
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
			else $rc = $this->quote(json_encode($value));
		} else if (is_array($value)) {
			$rc = $this->quote(json_encode($value));
		} else if (is_numeric($value)) {
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
	 * Replaces the #__ in a table name with the tablePrefix (if configured).
	 * @param string $s - the table name
	 * @param string the table name with prefix replaced
	 */
	protected function replaceTablePrefix($s) {
		$prefix = $this->dbconfig['tablePrefix'];
		if (!$prefix) $prefix = '';
		return str_replace('#__', $prefix, $s);
	}

}
