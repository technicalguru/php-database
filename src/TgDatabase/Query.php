<?php

namespace TgDatabase;

/**
  * Query is a simplified API for retrieving entities by composing Criterion objects. This is a very 
  * convenient approach for functionality like "search" screens where there is a variable number of 
  * conditions to be placed upon the result set.
  */
interface Query {

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function add(Criterion ...$criterion);

	/**
	  * Add an ordering to the result set.
	  */
	public function addOrder(Order ...$order);

	/**
	  * Set a projection for the query.
	  */
	public function setProjection(Projection $projection);

	/**
	  * Set the index of the first result to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function setFirstResult(int $firstResult);

	/**
	  * Set a limit upon the number of rows to be retrieved. 
	  * @return Query - this query for method chaining.
	  */
	public function setMaxResults(int $maxResults);

	/**
	  * Create a new join query.
	  */
	public function createJoinedQuery($tableName, $alias, $joinCriterion);

	/**
	  * Create a new join query.
	  * @deprecated Use #createJoinedQuery instead
	  */
	public function createCriteria($tableName, $alias, $joinCriterion);

	/**
	  * Add a new join query.
	  */
	public function addJoinedQuery(Query $query, $joinCriterion);

	/**
	  * Add a new join query.
	  * @deprecated Use #addJoinedQuery instead
	  */
	public function addCriteria(Query $query, $joinCriterion);

	/**
	  * Queries the database.
	  */
	public function list($throwException = FALSE);

	/**
	  * Queries the database and returns the first row.
	  */
	public function first($throwException = FALSE);

	/**
	 * Updates the database.
	 */
	public function save($fields, $throwException = FALSE);

	/**
	 * Deletes objects from database.
	 */
	public function delete($throwException = FALSE);

	/**
	 * Returns whether the database has an error.
	 * @return boolean TRUE when an error exists
	 */
	public function hasError();

	/**
	 * Returns the database error from the last action.
	 * @return string the error message
	 */
	public function error();

}
