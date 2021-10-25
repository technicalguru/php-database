<?php

namespace TgDatabase\Criterion;

use TgDatabase\Criteria;
use TgDatabase\Criterion;

class LogicalExpression implements Criterion {

	public function __construct($op, ...$criterions) {
		$this->op         = $op;
		if ((count($criterions) == 1) and is_array($criterions[0])) {
			$this->criterions = $criterions[0];
		} else {
			$this->criterions = $criterions;
		}
	}

	/**
	  * Dynamically add more criterions to the expression.
	  * @param mixed $criterions - more criterions to be added.
	  */
	public function add(...$criterions) {
		if ((count($criterions) == 1) and is_array($criterions[0])) {
			$this->criterions = array_merge($this->criterions, $criterions[0]);
		} else {
			$this->criterions = array_merge($this->criterions, $criterions);
		}
		return $this;
	}

	/**
	  * Render the SQL fragment.
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCriteria, $overallCriteria) {
		$rc = '';

		foreach ($this->criterions AS $criterion) {
			if (strlen($rc) > 0) {
				$rc .= ' '.$this->op.' ';
			}
			$rc .= '('.$criterion->toSqlString($localCriteria, $overallCriteria).')';
		}
		return $rc;
	}
	
}
