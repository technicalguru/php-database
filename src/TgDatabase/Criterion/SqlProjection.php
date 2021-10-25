<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Projection;

class SqlProjection implements Projection {

	public function __construct($sql) {
		$this->sql = $sql;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		return $this->sql;
	}
	
}
