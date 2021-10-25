<?php

namespace TgDatabase;

/**
 * An object-oriented representation of a query result set projection in a Criteria query. 
 * Built-in projection types are provided by the Projections factory class. This interface 
 * might be implemented by application classes that define custom projections. 
 */
interface Projection {

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCritera, $overallCriteria);

}
