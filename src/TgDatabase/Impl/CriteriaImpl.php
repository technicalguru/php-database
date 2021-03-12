<?php

namespace TgDatabase\Impl;

use TgDatabase\Criteria;
use TgDatabase\Criterion;
use TgDatabase\Order;
use TgDatabase\Projection;


class CriteriaImpl implements Criteria {

	public function __construct($database, $tableName, $resultClassName = NULL, $alias = NULL) {
		$this->database        = $database;
		$this->tableName       = $tableName;
		$this->resultClassName = $resultClassName != NULL ? $resultClassName : 'stdClass';
		$this->alias           = $alias;
		$this->projection      = NULL;
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
		$this->resultClassName = 'stdClass';
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
	  * Queries the database.
	  */
	public function list() {
		$sql = $this->toSqlString();
		return $this->database->queryList($sql, $this->resultClassName);
	}

	public function toSqlString() {
		// SELECT projections
		$rc .= $this->getSelectClause();

		// FROM table
		$rc .= ' '.$this->getFromClause();

		// JOIN not implemented yet
		$rc .= ' '.$this->getJoinClause();

		// GROUP BY projection not implemented yet
		$rc .= ' '.$this->getGroupByClause();

		// WHERE clauses
		$rc .= ' '.$this->getWhereClause();
		
		// ORDER BY clauses
		$rc .= ' '.$this->getOrderByClause();

		// LIMIT clause
		$rc .= ' '.$this->getLimitClause();

		return $rc;
	}

	protected function getSelectClause() {
		$rc = 'SELECT ';
		if ($this->projections != NULL) {
			$rc .= $projection->toSqlString($this, $this);
		} else {
			$rc .= '*';
		}
		return $rc;
	}

	protected function getFromClause() {
		$rc = 'FROM '.$this->quoteName($this->tableName);
		if ($this->alias != NULL) $rc .= ' AS '.$this->quoteName($this->alias);
		return $rc;
	}

	protected function getJoinClause() {
		return '';
	}

	protected function getGroupByClause() {
		return '';
	}

	protected function getWhereClause() {
		$rc = '';
		if (count($this->criterions) > 0) {
			$rc .= 'WHERE ';
			$first = TRUE;
			foreach ($this->criterions AS $criterion) {
				if (!$first) $rc .= ' AND ';
				$rc .= '('.$criterion->toSqlString($this, $this).')';
				$first = FALSE;
			}
		}
		return $rc;
	}

	protected function getOrderByClause() {
		$rc = '';
		if (count($this->orders) > 0) {
			$rc .= 'ORDER BY ';
			$first = TRUE;
			foreach ($this->orders AS $order) {
				if (!$first) $rc .= ',';
				$rc .= '('.$order->toSqlString($this, $this).')';
				$first = FALSE;
			}
		}
		return $rc;
	}

	protected function getLimitClause() {
		$rc = '';
		if ($this->maxResults > 0) {
			$rc = 'LIMIT '.$this->maxResults;
			if ($this->firstResult >= 0) {
				$rc .= ' OFFSET '.$this->firstResult;
			}
		}
		return $rc;
	}

	public function prepareValue($value, $lowerCase) {
		if ($lowerCase && is_string($value)) $value = strtolower($value);
		return $this->database->prepareValue($value);
	}

	public function quoteName($aliasOrIdentifier, $identifier = NULL) {
		$rc = $this->database->quoteName($aliasOrIdentifier);
		if ($identifier != NULL) $rc .= '.'.$this->database->quoteName($identifier);
		return $rc;
	}

}

