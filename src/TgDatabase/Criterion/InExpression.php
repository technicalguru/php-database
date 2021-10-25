<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Criterion;

class InExpression implements Criterion {

	public function __construct($propertyName, $values, $ignoreCase = FALSE) {
		$this->propertyName = $propertyName;
		$this->values       = $values;
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
		$lower = $this->ignoreCase;

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallCriteria->quoteName($localCriteria->getAlias(), $this->propertyName);
		if ($lower) {
			$rc .= ')';
		}
		$rc .= ' IN (';
		$values = '';
		foreach ($this->values AS $value) {
			if (strlen($values) > 0) $values .= ',';
			$values .= $overallCriteria->prepareValue($value, $lower);
		}
		$rc .= $values.')';
		return $rc;
	}
	
}
