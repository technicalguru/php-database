<?php

namespace Tgdatabase\Criterion;

class AvgProjection extends AggregateProjection {

	public function __construct($propertyName) {
		parent::__construct('AVG', $propertyName);
	}

}

