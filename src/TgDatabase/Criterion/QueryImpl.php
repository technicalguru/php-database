<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
use TgDatabase\Criterion;
use TgDatabase\Order;
use TgDatabase\Projection;
use TgDatabase\Expression;
use TgDatabase\Projections;


class QueryImpl implements Query {

	public $database;
	public $tableName;
	public $resultClassName;
	public $alias;
	public $columns;
	public $subqueries;
	public $groupBy;
	public $having;
	public $criterions;
	public $orders;
	public $firstResult;
	public $maxResults;

	public function __construct($database, $tableName, $resultClassName = NULL, $alias = NULL) {
		$this->database        = $database;
		$this->tableName       = $tableName;
		$this->resultClassName = $resultClassName;
		$this->alias           = $alias;
		$this->columns         = array();
		$this->subqueries      = array();
		$this->groupBy         = array();
		$this->having          = array();
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
		$rc->columns         = $this->columns;
		$rc->subqueries      = $this->subqueries;
		$rc->groupBy         = $this->groupBy;
		$rc->having          = $this->having;
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
	public function setResultClass(?string $name) {
		$this->resultClassName = $name;
		return $this;
	}

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function where(Criterion ...$criterions) {
		foreach ($criterions AS $criterion) $this->criterions[] = $criterion;
		return $this;
	}

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function add(Criterion ...$criterions) {
		return call_user_func_array(array($this, 'where'), $criterions);
	}

	/**
	  * Add an ordering to the result set.
	  */
	public function orderBy(Order ...$orders) {
		foreach ($orders AS $order) $this->orders[] = $order;
		return $this;
	}

	/**
	  * Add an ordering to the result set.
	  */
	public function addOrder(Order ...$orders) {
		return call_user_func_array(array($this, 'orderBy'), $orders);
	}

	/**
	  * Set projection for the query.
	  * Attention! This class removes any result class name from the query. Use #setResultClass() after calling.
	  * @deprecated Use #setColumns() instead
	  */
	public function setProjection(Expression ...$expressions) {
		return call_user_func_array(array($this, 'setSelect'), $expressions);
	}

	/**
	  * Set select columns for the query.
	  * Attention! This class removes any result class name from the query. Use #setResultClass() after calling.
	  */
	public function setSelect(Expression ...$expressions) {
		$this->columns = array();
		return call_user_func_array(array($this, 'select'), $expressions);
	}

	/**
	  * Add select columns for the query.
	  */
	public function select(Expression ...$expressions) {
		foreach ($expressions AS $c) {
			if ($c != NULL) $this->columns[] = $c;
		}
		return $this;
	}

	/**
	  * Add group by columns for the query.
	  */
	public function groupBy(Expression ...$expressions) {
		foreach ($expressions AS $c) {
			if ($c != NULL) $this->groupBy[] = $c;
		}
		return $this;
	}

	/**
	  * Add a restriction to constrain the group by result.
	  * @return Query - this query for method chaining.
	  */
	public function having(Criterion ...$criterions) {
		foreach ($criterions AS $criterion) $this->having[] = $criterion;
		return $this;
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
	public function createJoin($tableName, $alias, $joinCriterion) {
		$rc = new QueryImpl($this->database, $tableName, NULL, $alias);
		$this->join($rc, $joinCriterion);
		return $rc;
	}

	/**
	  * Create a new join query.
	  * @deprecated
	  */
	public function createCriteria($tableName, $alias, $joinCriterion) {
		return $this->createJoin($tableName, $alias, $joinCriterion);
	}

	/**
	  * Add a new join query.
	  */
	public function join(Query $query, $joinCriterion) {
		$this->subqueries[] = array($query, $joinCriterion);
		return $this;
	}

	/**
	  * Add a new join query.
	  * @deprecated
	  */
	public function addCriteria(Query $query, $joinCriterion) {
		return $this->join($query, $joinCriterion);
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
		$query  = $this->clone()->select(Projections::alias(Projections::rowCount(), 'cnt'))->setResultClass(NULL);
		// Remove all ORDER clauses
		$query->orders = array();
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
		// SELECT columns
		$rc = 'SELECT '.$this->getSelectClause();

		// FROM table
		$rc .= ' FROM '.$this->getFromClause();

		// JOIN not implemented yet
		$join = $this->getJoinClause();
		if ($join != NULL) {
		    $rc .= ' '.trim($join);
		}
		
		// WHERE clauses
		$where = $this->getWhereClause();
		if ($where != NULL) {
			$rc .= ' WHERE '.$where;
		}
		
		// GROUP BY
		$group = $this->getGroupByClause();
		if ($group != NULL) {
		    $rc .= ' GROUP BY '.$group;

			// HAVING
			$having = $this->getHavingClause();
			if ($having != NULL) {
				$rc .= ' HAVING '.$having;
			}
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
		if (($this->columns != NULL) && (count($this->columns) > 0)) {
			$sql = array();
			foreach ($this->columns AS $p) {
				$sql[] = $p->toSqlString($this, $this);
			}
			$rc .= implode(', ', $sql);
		} else if ($this->alias != NULL) {
			$rc .= $this->quoteName($this->alias).'.*';
		} else {
			$rc .= '*';
		}
		return $rc;
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
		$rc = NULL;
		if (($this->groupBy != NULL) && (count($this->groupBy) > 0)) {
			$sql = array();
			foreach ($this->groupBy AS $p) {
				$sql[] = $p->toSqlString($this, $this);
			}
			$rc = implode(', ', $sql);
		}
		return $rc;
	}

	public function getHavingClause() {
		$rc = NULL;
		if (count($this->having) > 0) {
			foreach ($this->having AS $criterion) {
				if ($rc != NULL) $rc .= ' AND ';
				else $rc = '';
				$rc .= '('.$criterion->toSqlString($this, $this).')';
			}
		}
		return $rc;
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

