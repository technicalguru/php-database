<?php

namespace TgDatabase;

/**
  * A general class for transforming table rows into data objects.
  * This class assumes a numerical, auto-incremental primary key!
  */
class DataModel {

	/** The database object */
	protected $database;

	/** The name of the data model class */
	protected $modelClass;

	/** The name of the data table */
	protected $tableName;

	/** The ID column */
	protected $idColumn;

	public function __construct($database, $tableName, $modelClass, $idColumn = 'uid') {
		$this->database   = $database;
		$this->tableName  = $tableName;
		$this->modelClass = class_exists($modelClass) ? $modelClass : 'stdClass';
		$this->idColumn   = $idColumn;
	}

	/** Get the object with given UID */
	public function get($uid) {
		return $this->database->querySingle('SELECT * FROM '.$this->database->quoteName($this->tableName).' WHERE '.$this->createCriterion($this->idColumn, $uid), $this->modelClass);
	}

	/** Find multiple objects by UID only - works on numeric IDs currently */
	public function findByUid($uids, $order = array()) {
		if (($uids == null) || !is_array($uids) || (count($uids) == 0)) return array();
		$where = $this->createCriterion($this->idColumn, $uids, 'IN');
		return $this->find($where, $order);
	}

	/** Find objects with given criteria and in given order */
	public function find($criteria = array(), $order = array(), $startIndex = -1, $maxObjects = 0) {
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

	/** Count objects with given criteria */
	public function count($criteria = array()) {
		$whereClause = $this->createWhereClause($criteria);
		$record = $this->database->querySingle('SELECT COUNT(*) AS cnt FROM '.$this->database->quoteName($this->tableName).' '.$whereClause);
		if ($record !== FALSE) {
			return $record->cnt;
		}
		return 0;
	}

	/** Find objects with given criteria and in given order */
	public function findSingle($criteria = array(), $order = array()) {
		$result = $this->find($criteria, $order, -1, 1);
		if (is_array($result) && (count($result)>0)) return $result[0];
		return NULL;
	}

	/** Create the given object */
	public function create($object) {
		$k   = $this->idColumn;
		$uid = $this->database->insert($this->database->quoteName($this->tableName), $this->preSave($object, true));
		if (is_numeric($uid)) $object->$k = $uid;
		return $uid;
	}

	/** Save the given object(s) */
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

	/** Models can override this function to eventually pre-process the objects value, e.g. normalizing or remove values.
	  * You always need to call parent::preSave($object, $isCreate).
	  * @return object the object the actual object to be persisted
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

	/** Delete the given object(s) */
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

	/** Get the full SQL query to delete the given uid.
	  * Override this to implement soft deletes.
	  */
	protected function getDeleteQuery($uid) {
		return 'DELETE FROM '.$this->database->quoteName($this->tableName).' WHERE '.$this->createCriterion($this->idColumn, $uid);
	}

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

}
