<?php

namespace TgDatabase\Criterion;

use TgDatabase\Expression;
use TgDatabase\Query;

class PropertySelect implements Expression {

	public function __construct($propertyName) {
		$this->propertyName = $propertyName;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return $overallQuery->quoteName($localQuery->getAlias(), $this->propertyName);
	}
}

