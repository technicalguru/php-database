<?php

namespace TgDatabase\Criterion;

use TgDatabase\Expression;
use TgDatabase\Query;

class AliasedProjection implements Expression {

	public function __construct($component, $alias) {
		$this->component = $component;
		$this->alias      = $alias;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return $this->component->toSqlString($localQuery, $overallQuery).' AS '.$overallQuery->quoteName($this->alias);
	}
}

