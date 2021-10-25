<?php

namespace TgDatabase\Criterion;

use TgDatabase\Projection;
use TgDatabase\Criteria;

class AliasedProjection implements Projection {

	public function __construct($projection, $alias) {
		$this->projection = $projection;
		$this->alias      = $alias;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		return $this->projection->toSqlString($localCriteria, $overallCriteria).' AS '.$overallCriteria->quoteName($this->alias);
	}
}

