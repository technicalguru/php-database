<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
use TgDatabase\Criterion;

class SimpleExpression implements Criterion {

	public $propertyName;
	public $value;
	public $op;
	public $ignoreCase;

	public function __construct($propertyName, $value, $op, $ignoreCase = FALSE) {
		$this->propertyName = $propertyName;
		$this->value        = $value;
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
		$lower = $this->ignoreCase && is_string($this->value);

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallQuery->quoteName($localQuery->getAlias(), $this->propertyName);
		if ($lower) {
			$rc .= ')';
		}
		$rc .= ' '.$this->op.' ';
		$rc .= $overallQuery->prepareValue($this->value, $lower);
		return $rc;
	}
	
}
