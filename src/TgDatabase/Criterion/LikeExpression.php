<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Criterion;

class LikeExpression implements Criterion {

	public function __construct($propertyName, $value, $ignoreCase = FALSE) {
		$this->propertyName = $propertyName;
		$this->value        = $value;
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
		$lower = $this->ignoreCase && is_string($this->value);

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallCriteria->quoteName($localCriteria->getAlias(), $this->propertyName);
		if ($lower) {
			$rc .= ')';
		}
		$rc .= ' LIKE ';
		$rc .= $overallCriteria->prepareValue($this->value, $lower);
		return $rc;
	}
	
}
