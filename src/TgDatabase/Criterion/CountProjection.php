<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;

class CountProjection extends AggregateProjection {

	public function __construct($propertyName, $distinct = FALSE) {
		parent::__construct('COUNT', $propertyName);
		$this->distinct = $distinct;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		$distinct = $this->distinct ? 'DISTINCT ' : '';
		return $this->functionName.'('.$distinct.$overallCriteria->quoteName($localCriteria->getAlias(), $this->propertyName).')';
	}
}

