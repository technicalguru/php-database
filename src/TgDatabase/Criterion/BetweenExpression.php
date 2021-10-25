<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Criterion;

class BetweenExpression implements Criterion {

	public function __construct($propertyName, $minValue, $maxValue, $ignoreCase = FALSE) {
		$this->propertyName = $propertyName;
		$this->minValue     = $minValue;
		$this->maxValue     = $maxValue;
		$this->ignoreCase   = $ignoreCase;
	}

	/**
	  * Impose case-insensitive restriction.
	  */
	public function ignoreCase() {
		$this->ignoreCase = TRUE;
		return $this;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		$lower = $this->ignoreCase && is_string($this->minValue) && is_string($this->maxValue);

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallCriteria->quoteName($localCriteria->getAlias(), $this->propertyName);
		if ($lower) {
			$rc .= ')';
		}
		$rc .= ' BETWEEN ';
		$rc .= $overallCriteria->prepareValue($this->minValue, $lower);
		$rc .= ' AND ';
		$rc .= $overallCriteria->prepareValue($this->maxValue, $lower);
		return $rc;
	}
	
}
