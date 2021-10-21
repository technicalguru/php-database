<?php

namespace TgDatabase;

/**
  * Criteria is a simplified API for retrieving entities by composing Criterion objects. This is a very 
  * convenient approach for functionality like "search" screens where there is a variable number of 
  * conditions to be placed upon the result set.
  */
interface Criteria {

	/**
	  * Add a restriction to constrain the results to be retrieved.
	  * @return Criteria - this criteria for method chaining.
	  */
	public function add(Criterion ...$criterion);

	/**
	  * Add an ordering to the result set.
	  */
	public function addOrder(Order ...$order);

	/**
	  * Set a projection for the criteria.
	  */
	public function setProjection(Projection $projection);

	/**
	  * Set the index of the first result to be retrieved.
	  * @return Criteria - this criteria for method chaining.
	  */
	public function setFirstResult(int $firstResult);

	/**
	  * Set a limit upon the number of rows to be retrieved. 
	  * @return Criteria - this criteria for method chaining.
	  */
	public function setMaxResults(int $maxResults);

	/**
	  * Create a new join criteria.
	  */
	public function createCriteria($tableName, $alias, $joinCriterion);

	/**
	  * Add a new join criteria.
	  */
	public function addCriteria(Criteria $criteria, $joinCriterion);

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
