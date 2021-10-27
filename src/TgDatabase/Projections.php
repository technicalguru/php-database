<?php

namespace TgDatabase;

use TgDatabase\Criterion\Distinct;
use TgDatabase\Criterion\PropertySelect;
use TgDatabase\Criterion\RowCountProjection;
use TgDatabase\Criterion\CountProjection;
use TgDatabase\Criterion\AggregateProjection;
use TgDatabase\Criterion\AliasedProjection;
use TgDatabase\Criterion\SqlProjection;

class Projections {

	public static function property($propertyName, $alias = NULL) {
		$rc = new PropertySelect($propertyName);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function distinct($projection) {
		return new Distinct($projection);
	}

	public static function rowCount($alias = NULL) {
		$rc = new RowCountProjection();
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function count($propertyName, $alias = NULL) {
		$rc = new CountProjection($propertyName);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function countDistinct($propertyName, $alias = NULL) {
		$rc = new CountProjection($propertyName, TRUE);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function avg($propertyName, $alias = NULL) {
		$rc = new AggregateProjection('AVG', $propertyName);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function max($propertyName, $alias = NULL) {
		$rc = new AggregateProjection('MAX', $propertyName);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function min($propertyName, $alias = NULL) {
		$rc = new AggregateProjection('MIN', $propertyName);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function sum($propertyName, $alias = NULL) {
		$rc = new AggregateProjection('SUM', $propertyName);
		if ($alias != NULL) $rc = self::alias($rc, $alias);
		return $rc;
	}

	public static function alias($projection, $alias) {
		return new AliasedProjection($projection, $alias);
	}
	
	public static function sql($sql) {
		return new SqlProjection($sql);
	}

	
}
