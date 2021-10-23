<?php

namespace TgDatabase;

/**
  * Represents an order imposed upon a Query result set.
  */
class Order {

	public function __construct($propertyName, $ascending = TRUE) {
		$this->propertyName = $propertyName;
		$this->ascending    = $ascending;
		$this->ignoreCase   = FALSE;
	}

	/*
	  * Impose case-insensitive ordering.
	  */
	public function ignoreCase() {
		$this->ignoreCase = TRUE;
		return $this;
	}

	/**
	  * Render the SQL fragment.
	  * @param Query $localQuery   - local criteria object (e.g. subquery)
	  * @param Query $overallQuery - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localQuery, $overallQuery) {
		$lower = $this->ignoreCase;

		$rc = '';
		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallQuery->quoteName($localQuery->getAlias(), $this->propertyName);
		if ($lower) {
			$rc .= ')';
		}

		if (!$this->ascending) {
			$rc .= ' DESC';
		}
		return $rc;
	}
	
	public static function asc($propertyName) {
		return new Order($propertyName);
	}

	public static function desc($propertyName) {
		return new Order($propertyName, FALSE);
	}

}

