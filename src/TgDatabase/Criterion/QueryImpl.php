<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
use TgDatabase\Criterion;
use TgDatabase\Order;
use TgDatabase\Projection;
use TgDatabase\SelectComponent;
use TgDatabase\Projections;


class QueryImpl implements Query {

	public function __construct($database, $tableName, $resultClassName = NULL, $alias = NULL) {
		$this->database        = $database;
		$this->tableName       = $tableName;
		$this->resultClassName = $resultClassName;
		$this->alias           = $alias;
		$this->projections     = array();
		$this->subqueries      = array();
		$this->criterions      = array();
		$this->orders          = array();
		$this->firstResult     = -1;
		$this->maxResults      = -1;
	}

	/**
	 * Clone this query.
	 */
	public function clone() {
		$rc = new QueryImpl($this->database, $this->tableName, $this->resultClassName, $this->alias);
		$rc->projections     = $this->projections;
		$rc->subqueries      = $this->subqueries;
		$rc->criterions      = $this->criterions;
		$rc->orders          = $this->orders;
		$rc->firstResult     = -1;
		$rc->maxResults      = -1;
		return $rc;
	}

	/**
	  * Resets the result class.
	  * Useful when using #setColumns() as this method erases the result class.
	  */
	public function setResultClass(string $name) {
		$this->resultClassName = $name;
		return $this;
	}

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function add(Criterion ...$criterions) {
		foreach ($criterions AS $criterion) $this->criterions[] = $criterion;
		return $this;
	}

	/**
	  * Add an ordering to the result set.
	  */
	public function addOrder(Order ...$orders) {
		foreach ($orders AS $order) $this->orders[] = $order;
		return $this;
	}

	/**
	  * Set projection for the query.
	  * Attention! This class removes any result class name from the query. Use #setResultClass() after calling.
	  * @deprecated Use #setColumns() instead
	  */
	public function setProjection(SelectComponent ...$components) {
		$this->projections = array();
		$this->_addColumns($components);
		$this->resultClassName = NULL;
		return $this;
	}

	/**
	  * Set select columns for the query.
	  * Attention! This class removes any result class name from the query. Use #setResultClass() after calling.
	  */
	public function setColumns(SelectComponent ...$components) {
		$this->projections = array();
		$this->_addColumns($components);
		$this->resultClassName = NULL;
		return $this;
	}

	/**
	  * Add select columns for the query.
	  */
	public function addColumns(SelectComponent ...$components) {
		$this->_addColumns($components);
		return $this;
	}

	/**
	  * Internal function to flatten array structure.
	  */
	protected function _addColumns($components) {
		if (is_array($components)) {
			foreach ($components AS $c) {
				$this->_addColumns($c);
			}
		} else {
			if ($components != NULL) $this->projections[] = $components;
		}
	}

	/**
	  * Set the index of the first result to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function setFirstResult(int $firstResult) {
		$this->firstResult = $firstResult;
		return $this;
	}

	/**
	  * Set a limit upon the number of rows to be retrieved. 
	  * @return Query - this query for method chaining.
	  */
	public function setMaxResults(int $maxResults) {
		$this->maxResults = $maxResults;
		return $this;
	}

	/**
	  * Returns the alias of this query.
	  */
	public function getAlias() {
		return $this->alias;
	}

	/**
	  * Create a new join query.
	  */
	public function createJoinedQuery($tableName, $alias, $joinCriterion) {
		$rc = new QueryImpl($this->database, $tableName, NULL, $alias);
		$this->addCriteria($rc, $joinCriterion);
		return $rc;
	}

	/**
	  * Create a new join query.
	  * @deprecated
	  */
	public function createCriteria($tableName, $alias, $joinCriterion) {
		return $this->createJoinedQuery($tableName, $alias, $joinCriterion);
	}

	/**
	  * Add a new join query.
	  */
	public function addJoinedQuery(Query $query, $joinCriterion) {
		$this->subqueries[] = array($query, $joinCriterion);
		return $this;
	}

	/**
	  * Add a new join query.
	  * @deprecated
	  */
	public function addCriteria(Query $query, $joinCriterion) {
		return $this->addJoinedQuery($query, $joinCriterion);
	}

	/**
	  * Queries the database and returns all defined rows.
	  */
	public function list($throwException = FALSE) {
		$sql = $this->getSelectSql();
		$rc = $this->database->queryList($sql, $this->resultClassName);
		if ($this->hasError() && $throwException) {
			throw new \Exception('Database error when querying: '.$this->error());
		}
		return $rc;
	}

	/**
	 * Count the results.
	 */
	public function count($throwException = FALSE) {
		$query  = $this->clone()->setProjection(Projections::alias(Projections::rowCount(), 'cnt'));
		$record = $query->first();
		if ($query->hasError()) {
			if ($throwException) {
				throw new \Exception('Database error when querying: '.$this->error());
			}
			return 0;
		}
		return $record->cnt;
	}

	/**
	  * Queries the database and returns only the first row.
	  */
	public function first($throwException = FALSE) {
		$sql = $this->getSelectSql();
		$rc = $this->database->querySingle($sql, $this->resultClassName);
		if ($this->hasError() && $throwException) {
			throw new \Exception('Database error when querying: '.$this->error());
		}
		return $rc;
	}

		/**
	 * Updates the database.
	 */
	public function save($fields, $throwException = FALSE) {
		$sql = $this->getUpdateSql($fields);
		$rc = $this->database->query($sql);
		if ((($rc === FALSE) || $this->hasError()) && $throwException) {
			throw new \Exception('Database error when updating: '.$this->error());
		}
		return $rc;
	}

	/**
	 * Deletes objects from database.
	 */
	public function delete($throwException = FALSE) {
		$sql = $this->getDeleteSql();
		$rc = $this->database->query($sql);
		if ((($rc === FALSE) || $this->hasError()) && $throwException) {
			throw new \Exception('Database error when deleting: '.$this->error());
		}
		return $rc;
	}

	public function getSelectSql() {
		// SELECT projections
		$rc = 'SELECT '.$this->getSelectClause();

		// FROM table
		$rc .= ' FROM '.$this->getFromClause();

		// JOIN not implemented yet
		$join = $this->getJoinClause();
		if ($join != NULL) {
		    $rc .= ' '.trim($join);
		}
		
		// GROUP BY projection not implemented yet
		$group = $this->getGroupByClause();
		if ($group != NULL) {
		    $rc .= ' '.$group;
		}
		
		// WHERE clauses
		$where = $this->getWhereClause();
		if ($where != NULL) {
			$rc .= ' WHERE '.$where;
		}
		
		// ORDER BY clauses
		$orderBy = $this->getOrderByClause();
		if ($orderBy != NULL) {
			$rc .= ' ORDER BY '.$orderBy;
		}

		// LIMIT clause
		$limit = $this->getLimitClause();
		if ($limit != NULL) {
			$rc .= ' LIMIT '.$limit;
		}

		return $rc;
	}

	public function getUpdateSql($fields) {
		// UPDATE
		$rc = 'UPDATE ';

		// FROM table
		$rc .= $this->getFromClause();

		// SET
		$rc .= ' SET '.$this->getSetClause($fields);

		// WHERE clauses
		$where = $this->getWhereClause();
		if ($where != NULL) {
			$rc .= ' WHERE '.$where;
		}
		
		return $rc;
	}

	public function getDeleteSql() {
		// DELETE
		$rc = 'DELETE';

		// FROM table
		$rc .= ' FROM '.$this->getFromClause();

		// WHERE clauses
		$where = $this->getWhereClause();
		if ($where != NULL) {
			$rc .= ' WHERE '.$where;
		}
		
		return $rc;
	}

	/**
	 * @deprecated Use #getSelectSql()
	 */
	public function toSqlString() {
		return $this->getSelectSql();
	}

	public function getSelectClause() {
		$rc = '';
		if (($this->projections != NULL) && (count($this->projections) > 0)) {
			$sql = array();
			foreach ($this->projections AS $p) {
				$sql[] = $p->toSqlString($this, $this);
			}
			$rc .= ' '.implode(', ', $sql);
		} else if ($this->alias != NULL) {
			$rc .= $this->quoteName($this->alias).'.*';
		} else {
			$rc .= '*';
		}
		return trim($rc);
	}

	public function getFromClause() {
		$rc = $this->quoteName($this->tableName);
		if ($this->alias != NULL) $rc .= ' AS '.$this->quoteName($this->alias);
		return $rc;
	}

	public function getJoinClause() {
	    $rc = NULL;
	    if (count($this->subqueries) > 0) {
        	$rc = '';
        	foreach ($this->subqueries AS list($query, $joinCriterion)) {
        		$rc .= ' INNER JOIN '.$query->getFromClause().' ON '.$joinCriterion->toSqlString($this, $query);
        	}
	    }
		return $rc;
	}

	public function getGroupByClause() {
		return NULL;
	}

	public function getWhereClause() {
		$rc = NULL;
		if (count($this->criterions) > 0) {
			foreach ($this->criterions AS $criterion) {
				if ($rc != NULL) $rc .= ' AND ';
				else $rc = '';
				$rc .= '('.$criterion->toSqlString($this, $this).')';
			}
		}

		// Add subquery where clauses
		foreach ($this->subqueries AS list($query, $joinCriterion)) {
			foreach ($query->criterions AS $criterion) {
				if ($rc != NULL) $rc .= ' AND ';
				else $rc = '';
				$rc .= '('.$criterion->toSqlString($query, $this).')';
			}
		}
		return $rc;
	}

	public function getOrderByClause() {
		$rc = NULL;
		if (count($this->orders) > 0) {
			$rc = '';
			$first = TRUE;
			foreach ($this->orders AS $order) {
				if (!$first) $rc .= ',';
				$rc .= $order->toSqlString($this, $this);
				$first = FALSE;
			}
			if (trim($rc) == '') $rc = NULL;
		}
		return $rc;
	}

	public function getLimitClause() {
		$rc = NULL;
		if ($this->maxResults > 0) {
			$rc = $this->maxResults;
			if ($this->firstResult >= 0) {
				$rc .= ' OFFSET '.$this->firstResult;
			}
		}
		return $rc;
	}

	public function getSetClause($fields) {
		if (is_object($fields)) $fields = get_object_vars($fields);
		$values = array();
		foreach ($fields AS $k => $v) {
			$value = $v;
			if ($v === NULL) $value = 'NULL';
			else $value = $this->prepareValue($v);
			$values[] = '`'.$k.'`='.$value;
		}
		return implode(', ', $values);
	}

	public function prepareValue($value, $lowerCase = FALSE) {
		if ($lowerCase && is_string($value)) $value = strtolower($value);
		return $this->database->prepareValue($value);
	}

	/**
	 * Quote the identifer (e.g. a table or attribute name).
	 * If the identifier must be qualified by an alias then function takes two arguments.
	 * @param mixed $aliasOrIdentifier - a string containing alias or identifier or an array containing both
	 * @param mixed $identifier        - the identifier string for the alias or an array of alias and identifier.
	 * @return the quoted identifier 
	 */
	public function quoteName($aliasOrIdentifier, $identifier = NULL) {
		return $this->database->quoteName($aliasOrIdentifier, $identifier);
	}

	/**
	 * Returns whether the database has an error.
	 * @return boolean TRUE when an error exists
	 */
	public function hasError() {
		return $this->database->hasError();
	}

	/**
	 * Returns the error from the database connection.
	 * @return string error text
	 */
	public function error() {
		return $this->database->error();
	}
}

