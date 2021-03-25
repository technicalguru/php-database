<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Criterion;
use TgDatabase\Order;
use TgDatabase\Projection;


class CriteriaImpl implements Criteria {

	public function __construct($database, $tableName, $resultClassName = NULL, $alias = NULL) {
		$this->database        = $database;
		$this->tableName       = $tableName;
		$this->resultClassName = $resultClassName;
		$this->alias           = $alias;
		$this->projection      = NULL;
		$this->subcriteria     = array();
		$this->criterions      = array();
		$this->orders          = array();
		$this->firstResult     = -1;
		$this->maxResults      = -1;
	}

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Criteria - this criteria for method chaining.
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
	  * Add a projection to the criteria.
	  */
	public function setProjection(Projection $projection) {
		$this->projection      = $projection;
		$this->resultClassName = NULL;
		return $this;
	}

	/**
	  * Set the index of the first result to be retrieved.
	  * @return Criteria - this criteria for method chaining.
	  */
	public function setFirstResult(int $firstResult) {
		$this->firstResult = $firstResult;
		return $this;
	}

	/**
	  * Set a limit upon the number of rows to be retrieved. 
	  * @return Criteria - this criteria for method chaining.
	  */
	public function setMaxResults(int $maxResults) {
		$this->maxResults = $maxResults;
		return $this;
	}

	/**
	  * Returns the alias of this criteria.
	  */
	public function getAlias() {
		return $this->alias;
	}

	/**
	  * Create a new join criteria.
	  */
	public function createCriteria($tableName, $alias, $joinCriterion) {
		$rc = new CriteriaImpl($this->database, $tableName, NULL, $alias);
		$this->addCriteria($rc, $joinCriterion);
		return $rc;
	}

	/**
	  * Add a new join criteria.
	  */
	public function addCriteria(Criteria $criteria, $joinCriterion) {
		$this->subcriteria[] = array($criteria, $joinCriterion);
		return $this;
	}

	/**
	  * Queries the database and returns all defined rows.
	  */
	public function list() {
		$sql = $this->toSqlString();
		\TgLog\Log::debug('criteriaQuery: '.$sql);
		return $this->database->queryList($sql, $this->resultClassName);
	}

	/**
	  * Queries the database and returns only the first row.
	  */
	public function first() {
		$sql = $this->toSqlString();
		\TgLog\Log::debug('criteriaQuery: '.$sql);
		return $this->database->querySingle($sql, $this->resultClassName);
	}

	public function toSqlString() {
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
	    if (count($this->subcriteria) > 0) {
        	$rc = '';
        	foreach ($this->subcriteria AS list($criteria, $joinCriterion)) {
        		$rc .= ' INNER JOIN '.$criteria->getFromClause().' ON '.$joinCriterion->toSqlString($this, $criteria);
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

		// Add subcriteria where clauses
		foreach ($this->subcriteria AS list($criteria, $joinCriterion)) {
			foreach ($criteria->criterions AS $criterion) {
				if ($rc != NULL) $rc .= ' AND ';
				else $rc = '';
				$rc .= '('.$criterion->toSqlString($criteria, $this).')';
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
	 * Returns the error from the database connection.
	 * @return string error text
	 */
	public function error() {
		return $this->error();
	}
}

