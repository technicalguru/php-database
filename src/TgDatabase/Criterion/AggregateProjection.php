<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Query;

class AggregateProjection implements Projection {

	public function __construct($functionName, $propertyName) {
		$this->functionName = $functionName;
		$this->propertyName = $propertyName;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return $this->functionName.'('.$overallQuery->quoteName($localQuery->getAlias(), $this->propertyName).')';
	}
}

