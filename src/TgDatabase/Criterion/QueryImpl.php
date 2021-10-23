<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
use TgDatabase\Criterion;
use TgDatabase\Order;
use TgDatabase\Projection;


class QueryImpl implements Query {

	public function __construct($database, $tableName, $resultClassName = NULL, $alias = NULL) {
		$this->database        = $database;
		$this->tableName       = $tableName;
		$this->resultClassName = $resultClassName;
		$this->alias           = $alias;
		$this->projection      = NULL;
		$this->subqueries     = array();
		$this->criterions      = array();
		$this->orders          = array();
		$this->firstResult     = -1;
		$this->maxResults      = -1;
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
	  * Add a projection to the query.
	  */
	public function setProjection(Projection $projection) {
		$this->projection      = $projection;
		$this->resultClassName = NULL;
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

		// SET
		$rc .= ' SET '.$this->getSetClause($fields);

		// FROM table
		$rc .= ' FROM '.$this->getFromClause();

		// WHERE clauses
		$where = $this->getWhereClause();
		if ($where != NULL) {
			$rc .= ' WHERE '.$where;
		}
		
		return $rc;
	}

	public function getDeleteSql() {
		// DELETE
		$rc = 'DELETE ';

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

	protected function getSelectClause() {
		$rc = '';
		if ($this->projection != NULL) {
			$rc .= $this->projection->toSqlString($this, $this);
		} else if ($this->alias != NULL) {
			$rc .= $this->quoteName($this->alias).'.*';
		} else {
			$rc .= '*';
		}
		return $rc;
	}

	protected function getFromClause() {
		$rc = $this->quoteName($this->tableName);
		if ($this->alias != NULL) $rc .= ' AS '.$this->quoteName($this->alias);
		return $rc;
	}

	protected function getJoinClause() {
	    $rc = NULL;
	    if (count($this->subqueries) > 0) {
        	$rc = '';
        	foreach ($this->subqueries AS list($query, $joinCriterion)) {
        		$rc .= ' INNER JOIN '.$query->getFromClause().' ON '.$joinCriterion->toSqlString($this, $query);
        	}
	    }
		return $rc;
	}

	protected function getGroupByClause() {
		return NULL;
	}

	protected function getWhereClause() {
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

	protected function getOrderByClause() {
		$rc = NULL;
		if (count($this->orders) > 0) {
			$rc = '';
			$first = TRUE;
			foreach ($this->orders AS $order) {
				if (!$first) $rc .= ',';
				$rc .= $order->toSqlString($this, $this);
				$first = FALSE;
			}
		}
		return $rc;
	}

	protected function getLimitClause() {
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

	public function quoteName($aliasOrIdentifier, $identifier = NULL) {
		if ($identifier != NULL) {
			if (is_array($identifier)) {
				return $this->database->quoteName($identifier[0]).'.'.$this->database->quoteName($identifier[1]);
			} else if ($aliasOrIdentifier != NULL) {
			    return $this->database->quoteName($aliasOrIdentifier).'.'.$this->database->quoteName($identifier);
			}
			return $this->database->quoteName($identifier);
		} else if (is_array($aliasOrIdentifier)) {
			return $this->database->quoteName($aliasOrIdentifier[0]).'.'.$this->database->quoteName($aliasOrIdentifier[1]);
		}
		return $this->database->quoteName($aliasOrIdentifier);
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

