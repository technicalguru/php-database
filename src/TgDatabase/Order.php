<?php

namespace TgDatabase;

/**
  * Represents an order imposed upon a Criteria result set.
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
	  * @param Criteria $localCriteria   - local criteria object (e.g. subquery)
	  * @param Criteria $overallCriteria - overall criteria object
	  * @return string - the SQL fragment representing this criterion.
	  */
	public function toSqlString($localCritera, $overallCriteria) {
		$lower = $this->ignoreCase;

		if ($lower) {
			$rc .= 'LOWER(';
		}
		$rc .= $overallCriteria->quoteName($this->propertyName);
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


