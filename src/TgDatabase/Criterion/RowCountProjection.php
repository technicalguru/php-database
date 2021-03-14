<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Criteria;

class RowCountProjection implements Projection {

	public function __construct() {
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		return 'COUNT(*)';
	}
}

