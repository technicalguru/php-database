<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Query;

class RowCountProjection implements Projection {

	public function __construct() {
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return 'COUNT(*)';
	}
}

