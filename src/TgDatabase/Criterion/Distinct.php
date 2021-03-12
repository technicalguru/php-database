<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Criteria;

class Distinct implements Projection {

	public function __construct($projection) {
		$this->projection = $projection;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		return 'DISTINCT '.$this->projection->toSqlString($localCriteria, $overallCriteria);
	}
}

