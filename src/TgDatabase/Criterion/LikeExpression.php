<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
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
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		$lower = $this->ignoreCase && is_string($this->value);

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallQuery->quoteName($localQuery->getAlias(), $this->propertyName);
		if ($lower) {
			$rc .= ')';
		}
		$rc .= ' LIKE ';
		$rc .= $overallQuery->prepareValue($this->value, $lower);
		return $rc;
	}
	
}
