<?php

namespace TgDatabase;

/**
  * Query is a simplified API for retrieving entities by composing Criterion objects. This is a very 
  * convenient approach for functionality like "search" screens where there is a variable number of 
  * conditions to be placed upon the result set.
  */
interface Query {

	/**
	  * Resets the result class.
	  * Useful when using #setColumns() as this method erases the result class.
	  * @return Query - this query for method chaining.
	  */
	public function setResultClass(string $name);

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function add(Criterion ...$criterion);

	/**
	  * Add an ordering to the result set.
	  * @return Query - this query for method chaining.
	  */
	public function addOrder(Order ...$order);

	/**
	  * Add select columns for the query.
	  * @return Query - this query for method chaining.
	  */
	public function addColumns(Expression ...$expressions);

	/**
	  * Set select columns for the query.
	  * Attention! This class removes any result class name from the query. Use #setResultClass() after calling.
	  * @return Query - this query for method chaining.
	  */
	public function setColumns(Expression ...$expressions);

	/**
	  * Add projections for the query.
	  * @return Query - this query for method chaining.
	  * @deprecated Use #setColumns()
	  */
	public function setProjection(Expression ...$expressions);

	/**
	  * Add group by column
	  * @return Query - this query for method chaining.
	  */
	public function groupBy(Expression ...$expressions);

	/**
	  * Add a restriction to constrain the group by result.
	  * @return Query - this query for method chaining.
	  */
	public function having(Criterion ...$criterions);

	/**
	  * Set the index of the first result to be retrieved.
	  * @return Query - this query for method chaining.
	  */
	public function setFirstResult(int $firstResult);

	/**
	  * Set a limit upon the number of rows to be retrieved. 
	  * @return Query - this query for method chaining.
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
	  * @return Query - this query for method chaining.
	  */
	public function addJoinedQuery(Query $query, $joinCriterion);

	/**
	  * Add a new join query.
	  * @return Query - this query for method chaining.
	  * @deprecated Use #addJoinedQuery instead
	  */
	public function addCriteria(Query $query, $joinCriterion);

	/**
	  * Queries the database.
	  */
	public function list($throwException = FALSE);

	/**
	 * Count the results.
	 */
	public function count($throwException = FALSE);

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
