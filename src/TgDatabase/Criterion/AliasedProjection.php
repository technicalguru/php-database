<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Query;

class AliasedProjection implements Projection {

	public function __construct($projection, $alias) {
		$this->projection = $projection;
		$this->alias      = $alias;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		return $this->projection->toSqlString($localQuery, $overallQuery).' AS '.$overallQuery->quoteName($this->alias);
	}
}

