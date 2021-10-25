<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Restrictions class.
 * @author ralph
 *
 */
final class RestrictionsTest extends TestCase {
    
    public function testEq(): void {
        $expr = Restrictions::eq('aName', 'aValue');
        $this->testSqlString('`aName` = \'aValue\'', $expr);
    }
        
    public function testEqOrNUll1(): void {
        $expr = Restrictions::eqOrNull('aName', 'aValue');
        $this->testSqlString('`aName` = \'aValue\'', $expr);
    }
        
    public function testEqOrNull2(): void {
        $expr = Restrictions::eqOrNull('aName', NULL);
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    public function testNe(): void {
        $expr = Restrictions::ne('aName', 'aValue');
        $this->testSqlString('`aName` != \'aValue\'', $expr);
    }
        
    public function testNeOrNull1(): void {
        $expr = Restrictions::neOrNull('aName', 'aValue');
        $this->testSqlString('`aName` != \'aValue\'', $expr);
    }
        
    public function testNeOrNull2(): void {
        $expr = Restrictions::neOrNull('aName', NULL);
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    public function testLike(): void {
        $expr = Restrictions::like('aName', 'a%Value');
        $this->testSqlString('`aName` LIKE \'a%Value\'', $expr);
    }
        
    public function testGt(): void {
        $expr = Restrictions::gt('aName', 'aValue');
        $this->testSqlString('`aName` > \'aValue\'', $expr);
    }
        
    public function testGe(): void {
        $expr = Restrictions::ge('aName', 'aValue');
        $this->testSqlString('`aName` >= \'aValue\'', $expr);
    }
        
    public function testlt(): void {
        $expr = Restrictions::lt('aName', 'aValue');
        $this->testSqlString('`aName` < \'aValue\'', $expr);
    }
        
    public function testle(): void {
        $expr = Restrictions::le('aName', 'aValue');
        $this->testSqlString('`aName` <= \'aValue\'', $expr);
    }
        
    public function testBetween(): void {
        $expr = Restrictions::between('aName', 'aValue1', 'aValue2');
        $this->testSqlString('`aName` BETWEEN \'aValue1\' AND \'aValue2\'', $expr);
    }
        
    public function testBetweenIgnoreCase(): void {
        $expr = Restrictions::between('aName', 'aValue1', 'aValue2')->ignoreCase();
        $this->testSqlString('LOWER(`aName`) BETWEEN \'avalue1\' AND \'avalue2\'', $expr);
    }
        
    public function testIsNull(): void {
        $expr = Restrictions::isNull('aName');
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    public function testIsNotNull(): void {
        $expr = Restrictions::isNotNull('aName');
        $this->testSqlString('`aName` IS NOT NULL', $expr);
    }
        
    public function testIn(): void {
        $expr = Restrictions::in('aName', array('aValue1','aValue2'));
        $this->testSqlString('`aName` IN (\'aValue1\',\'aValue2\')', $expr);
    }
        
    public function testNotIn(): void {
        $expr = Restrictions::notIn('aName', array('aValue1','aValue2'));
        $this->testSqlString('`aName` NOT IN (\'aValue1\',\'aValue2\')', $expr);
    }
        
    public function testAnd(): void {
        $expr = Restrictions::and(Restrictions::eq('aName1','aValue1'), Restrictions::eq('aName2','aValue2'));
        $this->testSqlString('(`aName1` = \'aValue1\') AND (`aName2` = \'aValue2\')', $expr);
    }
        
    public function testOr(): void {
        $expr = Restrictions::or(Restrictions::eq('aName1','aValue1'), Restrictions::eq('aName2','aValue2'));
        $this->testSqlString('(`aName1` = \'aValue1\') OR (`aName2` = \'aValue2\')', $expr);
    }
        
    public function testEqProperty(): void {
        $expr = Restrictions::eqProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` = `aName2`', $expr);
    }
        
    public function testNeProperty(): void {
        $expr = Restrictions::neProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` != `aName2`', $expr);
    }
        
    public function testGtProperty(): void {
        $expr = Restrictions::gtProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` > `aName2`', $expr);
    }
        
    public function testGeProperty(): void {
        $expr = Restrictions::geProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` >= `aName2`', $expr);
    }
        
    public function testLtProperty(): void {
        $expr = Restrictions::ltProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` < `aName2`', $expr);
    }
        
    public function testLeProperty(): void {
        $expr = Restrictions::leProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` <= `aName2`', $expr);
    }
        
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }

	public function testArguments(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` = \'value\'', Restrictions::toCriterion('attr', 'value')->toSqlString($query, $query));
		}
	}

	public function testArgumentArray(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` = \'value\'', Restrictions::toCriterion(array('attr', 'value'))->toSqlString($query, $query));
		}
	}

	public function testOperatorArgument(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` = \'value\'', Restrictions::toCriterion(array('attr', 'value', '='))->toSqlString($query, $query));
		}
	}

	public function testNullCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` IS NULL', Restrictions::toCriterion('attr', NULL)->toSqlString($query, $query));
		}
	}

	public function testNotNullCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` IS NOT NULL', Restrictions::toCriterion('attr', NULL, '!=')->toSqlString($query, $query));
		}
	}

	public function testNeCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` != \'value\'', Restrictions::toCriterion('attr', 'value', '!=')->toSqlString($query, $query));
		}
	}

	public function testGtCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` > \'value\'', Restrictions::toCriterion('attr', 'value', '>')->toSqlString($query, $query));
		}
	}

	public function testGeCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` >= \'value\'', Restrictions::toCriterion('attr', 'value', '>=')->toSqlString($query, $query));
		}
	}

	public function testLtCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` < \'value\'', Restrictions::toCriterion('attr', 'value', '<')->toSqlString($query, $query));
		}
	}

	public function testLeCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` <= \'value\'', Restrictions::toCriterion('attr', 'value', '<=')->toSqlString($query, $query));
		}
	}

	public function testLikeCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` LIKE \'value\'', Restrictions::toCriterion('attr', 'value', 'LIKE')->toSqlString($query, $query));
		}
	}

	public function testInCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` IN (\'value\')', Restrictions::toCriterion('attr', array('value'), 'IN')->toSqlString($query, $query));
		}
	}

	public function testNotInCriterion(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` NOT IN (\'value\')', Restrictions::toCriterion('attr', array('value'), 'NOT IN')->toSqlString($query, $query));
		}
	}

	public function testAndRestrictions(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals(
				'(`attr1` = \'value1\') AND (`attr2` != \'value2\')', 
				Restrictions::toRestrictions(array(
					array('attr1', 'value1'),
					array('attr2', 'value2', '!='),
				), 'and')
				->toSqlString($query, $query));
		}
	}

	public function testOrRestrictions(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals(
				'(`attr1` = \'value1\') OR (`attr2` != \'value2\')', 
				Restrictions::toRestrictions(array(
					array('attr1', 'value1'),
					array('attr2', 'value2', '!='),
				), 'or')
				->toSqlString($query, $query));
		}
	}

}