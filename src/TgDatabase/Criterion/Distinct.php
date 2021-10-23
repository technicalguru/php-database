<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Query;

class Distinct implements Projection {

	public function __construct($projection) {
		$this->projection = $projection;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return 'DISTINCT '.$this->projection->toSqlString($localQuery, $overallQuery);
	}
}

