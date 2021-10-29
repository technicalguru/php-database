<?php

namespace TgDatabase\Criterion;

use TgDatabase\Expression;
use TgDatabase\Query;

class MultiSelect implements Expression {

	public function __construct(Expression ...$components) {
		if ((count($components) == 1) && is_array($components[0])) {
			$this->components = $components[0];
		} else {
			$this->components = $components;
		}
	}

	/**
	 * Add another component in combination.
	 */
	public function add(Expression ...$components) {
		if ((count($components) == 1) && is_array($components[0])) {
			$arr = $components[0];
		} else {
			$arr = $components;
		}
		foreach ($arr AS $c) {
			$this->components[] = $c;
		}
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		$rc = array();
		foreach ($this->components AS $c) {
			$rc[] = $c->toSqlString($localQuery, $overallQuery);
		}
		return implode(', ', $rc);
	}
}

