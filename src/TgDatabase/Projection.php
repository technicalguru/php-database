<?php

namespace TgDatabase;

/**
 * An object-oriented representation of a query result set projection in a Query query. 
 * Built-in projection types are provided by the Projections factory class. This interface 
 * might be implemented by application classes that define custom projections. 
 */
interface Projection {

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local query object (e.g. subquery)
	  * @param Query $overallQuery - overall query object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery);

}
