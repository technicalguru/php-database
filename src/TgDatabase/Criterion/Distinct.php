<?php

namespace TgDatabase\Criterion;

use TgDatabase\SelectComponent;
use TgDatabase\Query;

class Distinct implements SelectComponent {

	public function __construct($component) {
		$this->component = $component;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return 'DISTINCT '.$this->component->toSqlString($localQuery, $overallQuery);
	}
}

