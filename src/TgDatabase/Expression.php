<?php

namespace TgDatabase;

/**
 * An object-oriented representation of a select clause part in a Query. 
 * Built-in select types are provided by the Projections factory class. This interface 
 * might be implemented by application classes that define custom select component. 
 */
interface Expression {

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local query object (e.g. subquery)
	  * @param Query $overallQuery - overall query object
	  * @return string - the SQL fragment representing this component.
	  */
	public function toSqlString($localQuery, $overallQuery);

}
