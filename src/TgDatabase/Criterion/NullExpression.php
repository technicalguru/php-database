<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Criterion;

class NullExpression implements Criterion {

	public function __construct($propertyName) {
		$this->propertyName = $propertyName;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		return $overallCriteria->quoteName($localCriteria->getAlias(), $this->propertyName).' IS NULL';
	}
	
}
