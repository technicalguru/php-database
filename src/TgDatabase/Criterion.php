<?php

namespace TgDatabase;

/**
 * An object-oriented representation of a query criterion that may be used as a restriction in a Criteria query. 
 * Built-in criterion types are provided by the Restrictions factory class. This interface might be implemented 
 * by application classes that define custom restriction criteria. 
 */
interface Criterion {

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCritera, $overallCriteria);

}
