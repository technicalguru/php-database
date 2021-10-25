<?php

namespace TgDatabase\Criterion;

use TgDatabase\Query;

class CountProjection extends AggregateProjection {

	public function __construct($propertyName, $distinct = FALSE) {
		parent::__construct('COUNT', $propertyName);
		$this->distinct = $distinct;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		$distinct = $this->distinct ? 'DISTINCT ' : '';
		return $this->functionName.'('.$distinct.$overallQuery->quoteName($localQuery->getAlias(), $this->propertyName).')';
	}
}

