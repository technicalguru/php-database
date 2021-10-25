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
use TgDatabase\Criterion\SqlExpression;

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
		if ((count($expressions) == 1) && is_array($expressions[0])) {
			return new LogicalExpression('AND', $expressions[0]);
		}
		return new LogicalExpression('AND', $expressions);
	}

	/**
	  * Apply an "or" conjunction.
	  */
	public static function or(...$expressions) {
		if ((count($expressions) == 1) && is_array($expressions[0])) {
			return new LogicalExpression('OR', $expressions[0]);
		}
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

	/**
	  * Apply a SQL constraint. (Exists as fallback to enable other expressions not supported yet)
	  */
	public static function sql($sql) {
		return new SqlExpression($sql);
	}

	// Flag that warns about deprectaion
	public static $hasDeprecatedUse = FALSE;

	/**
	 * Creates an array of Restriction objects.
	 * @param mixed  $restrictions - string or array of field clauses or Restriction objects - see README.md (optional)
	 * @param string $combine      - the logical operator to combine the restrictions (optional, default is AND)
	 * @return array of Restriction objects
	 */
	public static function toRestrictions($restrictions = NULL, $combine = 'AND') {
		self::$hasDeprecatedUse = FALSE;
		$rc = NULL;
		if ($restrictions != NULL) {
			if (is_array($restrictions)) {
				$rc = strtolower($combine) == 'and' ? Restrictions::and() : Restrictions::or();
				if (count($restrictions) > 0) {
					foreach ($restrictions AS $key => $value) {
						if (is_object($value) && is_a($value, 'TgDatabase\\Criterion')) {
							$rc->add($value);
						} else if (is_string($value) && !is_string($key)) {
							$rc->add(Restrictions::sql($value));
						} else if (is_array($value)) {
							$rc->add(self::toCriterion($value));
						} else {
							$rc->add(self::toCriterion($key, $value));
						}
					}
				}
			} else if (is_object($restrictions)) {
				$rc = $restrictions;
			} else if (is_string($restrictions)) {
				$rc = Restrictions::sql($restrictions);
			}
		}
		return $rc;
	}

	/**
	 * Creates a restriction from a field name, a value and an operator.
	 * @param string $field - the field name
	 * @param mixed $value - the field value to check for (can be NULL, an array or a specific value - optional, default is NULL)
	 * @param string $operator - criterion operator: one of =, !=, <=, >=, IN, NOT IN (optional, default is '=')
	 * @return Restriction object
	 */
	public static function toCriterion($field, $value = NULL, $operator = NULL) {
		$rc = NULL;
		if (is_object($field)) {
			$rc = $field;
		} else {
			self::$hasDeprecatedUse = TRUE;
			if (is_array($field)) {
				$value = $field[1];
				if (count($field) > 2) $operator = $field[2];
				$field = $field[0];
			}
			if ($operator == NULL) $operator = '=';
			
			if ($value === NULL) {
				$rc = $operator == '='  ? Restrictions::isNull($field) : Restrictions::isNotNull($field);
			} else {
				switch (strtolower($operator)) {
				case 'in':
					if (is_array($value)) $rc = Restrictions::in($field, $value);
					break;
				case 'not in':
					if (is_array($value)) $rc = Restrictions::notIn($field, $value);
					break;
				default:
					$rc = new Criterion\SimpleExpression($field, $value, $operator);
				}
			}
		}
		return $rc;
	}
	
}
