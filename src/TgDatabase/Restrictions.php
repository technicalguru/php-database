<?php

namespace TgDatabase;

use TgDatabase\Criterion\SimpleExpression;
use TgDatabase\Criterion\LogicalExpression;
use TgDatabase\Criterion\NullExpression;
use TgDatabase\Criterion\NotNullExpression;
use TgDatabase\Criterion\InExpression;
use TgDatabase\Criterion\NotInExpression;
use TgDatabase\Criterion\PropertyExpression;
use TgDatabase\Criterion\BetweenExpression;

/**
  * Provides the built-in citerions.
  */
class Restrictions {

	/**
	  * Apply an "equal" constraint to the named property.
	  */
	public static function eq($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, '=');
	}

	/**
	  * Apply an "equal" constraint to the named property. If
	  * the value is NULL apply a "is null" constraint.
	  */
	public static function eqOrNull($propertyName, $value) {
		return $value == NULL ? self::isNull($propertyName) : new SimpleExpression($propertyName, $value, '=');
	}

	/**
	  * Apply a "not equal" constraint to the named property.
	  */
	public static function ne($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, '!=');
	}

	/**
	  * Apply an "not equal" constraint to the named property. If
	  * the value is NULL apply a "is null" constraint.
	  */
	public static function neOrNull($propertyName, $value) {
		return $value == NULL ? self::isNull($propertyName) : new SimpleExpression($propertyName, $value, '!=');
	}

	/**
	  * Apply a "like" constraint to the named property.
	  */
	public static function like($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, 'LIKE');
	}

	/**
	  * Apply a "greater than" constraint to the named property.
	  */
	public static function gt($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, '>');
	}

	/**
	  * Apply a "greater or equal than" constraint to the named property.
	  */
	public static function ge($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, '>=');
	}

	/**
	  * Apply a "less than" constraint to the named property.
	  */
	public static function lt($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, '<');
	}

	/**
	  * Apply a "less or equal than" constraint to the named property.
	  */
	public static function le($propertyName, $value) {
		return new SimpleExpression($propertyName, $value, '<=');
	}

	/**
	  * Apply a "between" constraint to the named property.
	  */
	public static function between($propertyName, $minValue, $maxValue) {
		return new BetweenExpression($propertyName, $minValue, $maxValue);
	}

	/**
	  * Apply a "is null" constraint to the named property.
	  */
	public static function isNull($propertyName) {
		return new NullExpression($propertyName);
	}

	/**
	  * Apply a "is not null" constraint to the named property.
	  */
	public static function isNotNull($propertyName) {
		return new NotNullExpression($propertyName);
	}

	/**
	  * Apply a "in" constraint to the named property.
	  */
	public static function in($propertyName, $values) {
		return new InExpression($propertyName, $values);
	}

	/**
	  * Apply a "not in" constraint to the named property.
	  */
	public static function notIn($propertyName, $values) {
		return new NotInExpression($propertyName, $values);
	}

	/**
	  * Apply an "and" conjunction.
	  */
	public static function and(...$expressions) {
		return new LogicalExpression('AND', $expressions);
	}

	/**
	  * Apply an "or" conjunction.
	  */
	public static function or(...$expressions) {
		return new LogicalExpression('OR', $expressions);
	}

	/**
	  * Apply an "equal" constraint to two named property.
	  */
	public static function eqProperty($propertyName1, $propertyName2) {
		return new PropertyExpression($propertyName1, $propertyName2, '=');
	}

	/**
	  * Apply a "not equal" constraint to two named property.
	  */
	public static function neProperty($propertyName1, $propertyName2) {
		return new PropertyExpression($propertyName1, $propertyName2, '!=');
	}

	/**
	  * Apply a "greater than" constraint to two named property.
	  */
	public static function gtProperty($propertyName1, $propertyName2) {
		return new PropertyExpression($propertyName1, $propertyName2, '>');
	}

	/**
	  * Apply a "greater than or equal" constraint to two named property.
	  */
	public static function geProperty($propertyName1, $propertyName2) {
		return new PropertyExpression($propertyName1, $propertyName2, '>=');
	}

	/**
	  * Apply a "less than" constraint to two named property.
	  */
	public static function ltProperty($propertyName1, $propertyName2) {
		return new PropertyExpression($propertyName1, $propertyName2, '<');
	}

	/**
	  * Apply a "less than or equal" constraint to two named property.
	  */
	public static function leProperty($propertyName1, $propertyName2) {
		return new PropertyExpression($propertyName1, $propertyName2, '<=');
	}


}
