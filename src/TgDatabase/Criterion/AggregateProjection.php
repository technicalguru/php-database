<?php

namespace Tgdatabase\Criterion;

use TgDatabase\Projection;

class AggregateProjection implements Projection {

	public function __construct($functionName, $propertyName) {
		$this->functionName = $functionName;
		$this->propertyName = $propertyName;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		return $this->functionName.'('.$overallCriteria->quoteName($localCriteria->getAlias(), $this->propertyName).')';
	}
}

