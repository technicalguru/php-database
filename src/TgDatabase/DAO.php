<?php

namespace TgDatabase;

/**
  * A general DAO for accessing and manipulating table rows as objects.
  * <p>Essentially turning the relational database model into an object-relational model for PHP.</p>
  * <p>This class assumes a numerical, auto-incremental primary key!</p>
  */
class DAO {

	/** The database object */
	protected $database;

	/** The name of the data model class */
	protected $modelClass;

	/** The name of the data table */
	protected $tableName;

	/** The ID column */
	protected $idColumn;

	/**
	 * Constructor.
	 * @param Database $database - the database object
	 * @param string  $tableName  - the table name this object will handle (can include #__ as prefix)
	 * @param string  $modelClass - the name of the class that rows will be converted to (optional, default is \stdClass).
	 * @param string  $idColumn   - the name of the integer, auto-incremental primary key column (optional, default is uid)
	 * @param boolean $checkTable - whether to check existance of table and create if required (optional, default is FALSE)
	 */
	public function __construct($database, $tableName, $modelClass = 'stdClass', $idColumn = 'uid', $checkTable = FALSE) {
		$this->database   = $database;
		$this->tableName  = $tableName;
		$this->modelClass = class_exists($modelClass) ? $modelClass : 'stdClass';
		$this->idColumn   = $idColumn;
		if ($checkTable) $this->checkTable();
	}

	/**
	 * Checks the underlying table for existance and calls #createTable() if it does not exist.
	 * @return boolean TRUE when table exists or was created successfully.
	 */
	public function checkTable() {
		if (!$this->tableExists()) {
			return $this->createTable();
		}
		return TRUE;
	}

	/**
	 * Creates the table. Default implementation does nothing and returns FALSE.
	 * @return TRUE when table was created successfully.
	 */
	public function createTable() {
		return FALSE;
	}

	/**
	 * Checks existance of the underlying table.
	 * @return string TRUE when table exists, FALSE otherwise.
	 */
	public function tableExists() {
		return $this->database->tableExists($this->tableName);
	}

	/**
	 * Describes the underlying table.
	 * {
     *   "Field":   "uid",
     *   "Type":    "int(10) unsigned",
     *   "Null":    "NO",
     *   "Key":     "PRI",
     *   "Default": null,
     *   "Extra":   "auto_increment"
     * }
	 * @return array of columns (empty when error occured).
	 */
	public function describeTable() {
		return $this->database->describeTable($this->tableName);
	}

	/**
	 * Returns the error from the database connection.
	 * @return string error text
	 */
	public function error() {
		return $this->database->error();
	}

	/**
	 * Creates a new criteria object for this DAO.
	 * @param array $restrictions - the criterions to search for (AND) - see README.md (optional, default is empty array)
	 * @param array $order - list of order columns - see README.md (optional, default is empty array)
	 * @param int $startIndex - index of first object in order to return (optional, default is 0)
	 * @param int $maxObjects - number of objects to return at max (optional, default is 0 = all objects)
	 * @return the criteria object
	 */
	public function createCriteria($alias = NULL, $restrictions = array(), $order = array(), $startIndex = 0, $maxObjects = 0) {
		$criteria = $this->database->createCriteria($this->tableName, $this->modelClass, $alias);
		// Add restrictions
		$restrictions = self::toRestrictions($restrictions);
		if ($restrictions != NULL) $criteria->add($restrictions);

		// Add orders
		if (!is_array($order)) $order = array($order);
		foreach ($order AS $o) $criteria->addOrder(self::toOrder($o));

		// Limit result
		if ($startIndex >= 0) $criteria->setFirstResult($startIndex);
		if ($maxObjects >  0) $criteria->setMaxResults($maxObjects);
		return $criteria;
	}

	/** 
	 * Get the object with given id.
	 * @param int $uid - ID of object (row)
	 * @return object the object fetched (can be NULL)
	 */
	public function get($uid) {
		return $this->database->querySingle('SELECT * FROM '.$this->database->quoteName($this->tableName).' WHERE '.$this->createCriterion($this->idColumn, $uid), $this->modelClass);
	}

	/** 
	 * Find multiple objects by ID.
	 * <p>Returns all objects that appear in list of IDs.</p>
	 * @param array $uids - list of int IDs to find
	 * @param array $order - list of order columns (see README.md)
	 * @return array all objects (rows) found 
	 */
	public function findByUid($uids, $order = array()) {
		if (($uids == null) || !is_array($uids) || (count($uids) == 0)) return array();
		$where = $this->createCriterion($this->idColumn, $uids, 'IN');
		return $this->find($where, $order);
	}

	/** 
	 * Find objects with given criteria and in given order.
	 * @param array $criteria - the criterions to search for (AND) - see README.md (optional, default is empty array)
	 * @param array $order - list of order columns - see README.md (optional, default is empty array)
	 * @param int $startIndex - index of first object in order to return (optional, default is 0)
	 * @param int $maxObjects - number of objects to return at max (optional, default is 0 = all objects)
	 * @return array list of objects found matching the criteria
	 */
	public function find($criteria = array(), $order = array(), $startIndex = 0, $maxObjects = 0) {
		$whereClause = $this->createWhereClause($criteria);
		$orderClause = $this->createOrderClause($order);
		$limit       = '';
		if ($maxObjects > 0) {
			if ($startIndex >= 0) {
				$limit = ' LIMIT '.$maxObjects.' OFFSET '.$startIndex;
			} else {
				$limit = ' LIMIT '.$maxObjects.' OFFSET 0';
			}
		}
		return $this->database->queryList('SELECT * FROM '.$this->database->quoteName($this->tableName).' '.$whereClause.' '.$orderClause.$limit, $this->modelClass);
	}

	/** Count objects with given criteria.
	 * @param array $criteria - the criterions to search for (AND) - see README.md (optional, default is empty array)
	 * @return int the number of objects matching the criteria.
	 */
	public function count($criteria = array()) {
		$whereClause = $this->createWhereClause($criteria);
		$record = $this->database->querySingle('SELECT COUNT(*) AS cnt FROM '.$this->database->quoteName($this->tableName).' '.$whereClause);
		if ($record !== FALSE) {
			return $record->cnt;
		}
		return 0;
	}

	/** 
	 * Find first object with given criteria.
	 * @param array $criteria - the criterions to search for (AND) - see README.md (optional, default is empty array)
	 * @param array $order - list of order columns - see README.md (optional, default is empty array)
	 * @return object the first object found or NULL
	 */
	public function findSingle($criteria = array(), $order = array()) {
		$result = $this->find($criteria, $order, -1, 1);
		if (is_array($result) && (count($result)>0)) return $result[0];
		return NULL;
	}

	/**
	 * Creates a new instance of the model class.
	 * @return object - instance
	 */
	public function newInstance() {
		$name = $this->modelClass;
		return new $name();
	}

	/** 
	 * Create the given object in the database.
	 * @param object $object - object to be created
	 * @return int ID of new object
	 */
	public function create($object) {
		$k   = $this->idColumn;
		$uid = $this->database->insert($this->database->quoteName($this->tableName), $this->preSave($object, true));
		if (is_numeric($uid)) $object->$k = $uid;
		return $uid;
	}

	/** 
	 * Save the given object(s).
	 * @param mixed $object - array of objects or single object to be updated
	 * @return mixed - FALSE when save was incomplete (multiple objects) or failed, the updated object (single) or TRUE (multiple)
	 */
	public function save($object) {
		if (is_array($object)) {
			foreach ($object AS $o) {
				$rc = $this->save($o);
				if ($rc === FALSE) return FALSE;
			}
			return TRUE;
		} else {
			$fields = array();
			$uid    = 0;
			// Unset UID column and internal ones (prefixed with _)
			foreach (get_object_vars($this->preSave($object, false)) AS $field => $value) {
				if ($field == $this->idColumn) {
					$uid = $value;
				} else {
					$fields[$field] = $value;
				}
			}
			if (($uid != NULL) && (count($fields) > 0)) {
				return $this->database->updateSingle($this->database->quoteName($this->tableName), $fields, $this->createCriterion($this->idColumn, $uid));
			}
		}
		return FALSE;
	}

	/** 
	 * Models can override this function to eventually pre-process the objects' values, e.g. normalizing or remove values.
	 * <p>You always need to call parent::preSave($object, $isCreate).</p>
	 * <p>The default behaviour is to stripe off fields starting with an underscore (_).</p>
	 * @param object $object - the object to be persisted
	 * @param boolean $isCreate - TRUE when this is a new object to be created
	 * @return object the actual object to be persisted
	 */
	protected function preSave($object, $isCreate) {
		$rc = new \stdClass;
		foreach (get_object_vars($object) AS $field => $value) {
			if (substr($field, 0, 1) != '_') {
				$rc->$field = $value;
			}
		}
		return $rc;
	}

	/** 
	 * Delete the given object(s).
	 * @param mixed $object - array of objects, single object or array of IDs or single ID of objects to be deleted
	 * @return mixed - FALSE when delete was incomplete or failed, or TRUE
	 */
	public function delete($object) {
		if (is_array($object)) {
			foreach ($object AS $o) {
				$rc = $this->delete($o);
				if ($rc === FALSE) return FALSE;
			}
			return TRUE;
		} else {
			$uid = NULL;
			if (is_object($object)) {
				$k   = $this->idColumn;
				$uid = $object->$k;
			} else {
				$uid = $object;
			}
			if ($uid != NULL) {
				$query = $this->getDeleteQuery($uid);
				return $this->database->query($query);
			}
		}
		return FALSE;
	}


	/** 
	 * Delete the objects using the given criteria.
	 * @param array $criteria - the criterions to match for delete for (AND) - see README.md (optional, default will clear table)
	 * @return mixed - FALSE when delete failed, TRUE when successful
	 */
	public function deleteBy($criteria = array()) {
	    $whereClause = $this->createWhereClause($criteria);
	    return $this->database->delete($this->tableName, substr($whereClause, 6));
	}

	/**
	 * Returns the next auto increment value for this class. (Use with care!)
	 * @return int - next auto increment value as UID.
	 */	
	public function getNextUid() {
		return $this->database->getNextUid($this->tableName);
	}

	/** 
	 * Get the full SQL query to delete the given uid.
	 * <p>Override this to implement soft delete functionality.</p>
	 * @param int uid - ID of object to be deleted
	 * @return string the correct DELETE statement to delete this object or mark it as deleted.
	 */
	protected function getDeleteQuery($uid) {
		return 'DELETE FROM '.$this->database->quoteName($this->tableName).' WHERE '.$this->createCriterion($this->idColumn, $uid);
	}

	/**
	 * Creates a WHERE clause.
	 * @param mixed $criteria - string or array of field clauses - see README.md (optional)
	 * @param string $combine - the logical operator to combine the criteria (optional, default is AND)
	 * @return string a WHERE clause to be used
	 */
	protected function createWhereClause($criteria = NULL, $combine = 'AND') {
		$whereClause = '';
		if ($criteria != NULL) {
			if (is_array($criteria)) {
				if (count($criteria) > 0) {
					$where = '';
					foreach ($criteria AS $key => $value) {
						if ($where) $where .= ' '.$combine.' ';
						if (is_string($value) && !is_string($key)) {
							$where .= $value;
						} else if (is_array($value)) {
							$where .= $this->createCriterion($value);
						} else {
							$where .= $this->createCriterion($key, $value);
						}
					}
					$whereClause = 'WHERE '.$where;
				}
			} else if (is_string($criteria)) {
				$whereClause = 'WHERE '.$criteria;
			}
		}
		return $whereClause;
	}

	/**
	 * Creates an array of Restriction objects.
	 * @param mixed  $restrictions - string or array of field clauses or Restriction objects - see README.md (optional)
	 * @param string $combine      - the logical operator to combine the criteria (optional, default is AND)
	 * @return array of Restriction objects
	 */
	public static function toRestrictions($restrictions = NULL, $combine = 'AND') {
		$rc = NULL;
		if ($restrictions != NULL) {
			if (is_array($restrictions)) {
				$rc = strtolower($combine) == 'and' ? Restrictions::and() : Restrictions::or();
				if (count($restrictions) > 0) {
					foreach ($restrictions AS $key => $value) {
						if (is_object($value) && is_a($value, 'TgDatabase\\Criterion')) {
							$rc->add(§value);
						} else if (is_string($value) && !is_string($key)) {
							$rc->add(Restrictions::sql($value));
						} else if (is_array($value)) {
							$rc->add(self::toCriterion($value));
						} else {
							$rc->add(self::toCriterion($key, $value));
						}
					}
				}
			} else if (is_object($restrictions)) {
				$rc = $restrictions;
			} else if (is_string($restrictions)) {
				$rc = Restrictions::sql($restrictions);
			}
		}
		return $rc;
	}

	/**
	 * Creates a single criterion from a field name, a value and an operator.
	 * <p>Values will be quoted and escaped if required</p>
	 * @param string $field - the field name
	 * @param mixed $value - the field value to check for (can be NULL, an array or a specific value - optional, default is NULL)
	 * @param string $operator - criterion operator: one of =, !=, <=, >=, IN, NOT IN (optional, default is '=')
	 * @return string the single criterion that can be used in a WHERE clause
	 */
	protected function createCriterion($field, $value = NULL, $operator = NULL) {
		if (is_array($field)) {
			$value = $field[1];
			if (count($field) > 2) $operator = $field[2];
			$field = $field[0];
		}
		if ($operator == NULL) $operator = '=';

		$rc = $this->database->quoteName($field);
		if ($value === null) {
			$rc .= ($operator == '=' ? ' IS NULL' : ' IS NOT NULL');
		} else if (($operator == 'IN') || ($operator == 'NOT IN') && is_array($value)) {
			$values = array();
			foreach ($value AS $v) {
				$values[] = $this->database->prepareValue($v);
			}
			$rc .= ' '.$operator.' ('.implode(',', $values).')';
		} else {
			$rc .= $operator.$this->database->prepareValue($value);
		}
		return '('.$rc.')';
	}

	/**
	 * Creates the order clause.
	 * @param mixed $orders - string or array of order clauses (fieldname ASC/DESC) (optional, default is NULL)
	 * @return string correct ORDER clause
	 */
	protected function createOrderClause($orders = NULL) {
		$rc = '';
		if ($orders != NULL) {
			if (is_array($orders)) {
				if (count($orders) > 0) {
					$rc = 'ORDER BY '.implode(',', $orders);
				}
			} else if (is_string($orders)) {
				$rc = 'ORDER BY '.$orders;
			}
		}
		return $rc;
	}

	/**
	 * Creates a restriction from a field name, a value and an operator.
	 * @param string $field - the field name
	 * @param mixed $value - the field value to check for (can be NULL, an array or a specific value - optional, default is NULL)
	 * @param string $operator - criterion operator: one of =, !=, <=, >=, IN, NOT IN (optional, default is '=')
	 * @return Restriction object
	 */
	public static function toCriterion($field, $value = NULL, $operator = NULL) {
		$rc = NULL;
		if (is_object($field)) {
			$rc = $field;
		} else {
			if (is_array($field)) {
				$value = $field[1];
				if (count($field) > 2) $operator = $field[2];
				$field = $field[0];
			}
			if ($operator == NULL) $operator = '=';
			
			if ($value === NULL) {
				$rc = $operator == '='  ? Restrictions::isNull($field) : Restrictions::isNotNull($field);
			} else {
				switch (strtolower($operator)) {
				case 'in':
					if (is_array($value)) $rc = Restrictions::in($field, $value);
					break;
				case 'not in':
					if (is_array($value)) $rc = Restrictions::notIn($field, $value);
					break;
				default:
					$rc = new Criterion\SimpleExpression($field, $value, $operator);
				}
			}
		}
		return $rc;
	}

	/**
	 * Creates the order object.
	 * @param mixed $orders - string or order object (fieldname ASC/DESC)
	 * @return object new Order object
	 */
	public static function toOrder($order) {
		if (is_object($order)) return $order;

		$s = trim($s);
        $pos = strrpos($s, ' ');
        if ($pos > 0) {
            $lastWord = strtolower(substr($s, $pos+1));
            if ($lastWord == 'desc') return Order::desc(substr($s, 0, $pos));
            if ($lastWord == 'asc') return Order::asc(substr($s, 0, $pos));
            return Order::asc($s);
        }
		return Order::asc($s);
	}
}
