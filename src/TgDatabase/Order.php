<?php

namespace TgDatabase;

/**
  * Represents an order imposed upon a Query result set.
  */
class Order {

	public $propertyName;
	public $ascending;
	public $ignoreCase;
	public $sql;

	public function __construct($propertyName, $ascending = TRUE, $isSql = FALSE) {
		$this->propertyName = $propertyName;
		$this->ascending    = $ascending;
		$this->ignoreCase   = FALSE;
		$this->sql          = $isSql;
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
		if (!$this->sql) {
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
		} else {
			$rc = $this->propertyName;
		}
		return $rc;
	}
	
	public static function asc($propertyName) {
		return new Order($propertyName);
	}

	public static function desc($propertyName) {
		return new Order($propertyName, FALSE);
	}

	public static function sql($sql) {
		return new Order($sql, TRUE, TRUE);
	}

	/** Warn about deprecated usage */
	public static $hasDeprecatedUse = FALSE;

	/**
	 * Creates the order object.
	 * @param mixed $orders - string or order object (fieldname ASC/DESC)
	 * @return object new Order object
	 */
	public static function toOrder($order) {
		self::$hasDeprecatedUse = FALSE;
		if (is_object($order)) return $order;
		// It is more complicated, basically commata "," shall not be in there 
		// (but conflicts with parantheses and arguments
		//self::$hasDeprecatedUse = TRUE;
		return Order::sql($order);
	}

}

