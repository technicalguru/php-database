<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
use TgDatabase\Criterion;

class PropertyExpression implements Criterion {

	public function __construct($propertyName1, $propertyName2, $op, $ignoreCase = FALSE) {
		$this->propertyName1 = $propertyName1;
		$this->propertyName2 = $propertyName2;
		$this->op           = $op;
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
		$lower = $this->ignoreCase;

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallQuery->quoteName($localQuery->getAlias(), $this->propertyName1);
		if ($lower) {
			$rc .= ')';
		}
		$rc .= ' '.$this->op.' ';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallQuery->quoteName($localQuery->getAlias(), $this->propertyName2);
		if ($lower) {
			$rc .= ')';
		}

		return $rc;
	}
	
}
