<?php

namespace TgDatabase;

/**
 * An object-oriented representation of a query criterion that may be used as a restriction in a Query query. 
 * Built-in criterion types are provided by the Restrictions factory class. This interface might be implemented 
 * by application classes that define custom restriction query. 
 */
interface Criterion {

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local query object (e.g. subquery)
	  * @param Query $overallQuery - overall query object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery);

}
