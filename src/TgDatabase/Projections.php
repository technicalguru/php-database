<?php

namespace TgDatabase;

use TgDatabase\Criterion\Distinct;
use TgDatabase\Criterion\RowCountProjection;
use TgDatabase\Criterion\CountProjection;
use TgDatabase\Criterion\AggregateProjection;
use TgDatabase\Criterion\AliasedProjection;
use TgDatabase\Criterion\SqlProjection;

class Projections {

	public static function distinct(Projection $projection) {
		return new Distinct($projection);
	}

	public static function rowCount() {
		return new RowCountProjection();
	}

	public static function count($propertyName) {
		return new CountProjection($propertyName);
	}

	public static function countDistinct($propertyName) {
		return new CountProjection($propertyName, TRUE);
	}

	public static function avg($propertyName) {
		return new AggregateProjection('AVG', $propertyName);
	}

	public static function max($propertyName) {
		return new AggregateProjection('MAX', $propertyName);
	}

	public static function min($propertyName) {
		return new AggregateProjection('MIN', $propertyName);
	}

	public static function sum($propertyName) {
		return new AggregateProjection('SUM', $propertyName);
	}

	public static function alias($projection, $alias) {
		return new AliasedProjection($projection, $alias);
	}
	
	public static function sql($sql) {
		return new SqlProjection($sql);
	}

	
}
