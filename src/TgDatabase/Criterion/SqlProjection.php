<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;
use TgDatabase\Projection;

class SqlProjection implements Projection {

	public function __construct($sql) {
		$this->sql = $sql;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return $this->sql;
	}
	
}
