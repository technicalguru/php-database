<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;

/**
 * Tests the DAO class for the new criteria classes.
 * @author ralph
 *
 */
final class DaoTest extends TestCase {

	public function testArguments(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` = \'value\'', DAO::toCriterion('attr', 'value')->toSqlString($crit, $crit));
		}
	}

	public function testArgumentArray(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` = \'value\'', DAO::toCriterion(array('attr', 'value'))->toSqlString($crit, $crit));
		}
	}

	public function testOperatorArgument(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` = \'value\'', DAO::toCriterion(array('attr', 'value', '='))->toSqlString($crit, $crit));
		}
	}

	public function testNull(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` IS NULL', DAO::toCriterion('attr', NULL)->toSqlString($crit, $crit));
		}
	}

	public function testNotNull(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` IS NOT NULL', DAO::toCriterion('attr', NULL, '!=')->toSqlString($crit, $crit));
		}
	}

	public function testNe(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` != \'value\'', DAO::toCriterion('attr', 'value', '!=')->toSqlString($crit, $crit));
		}
	}

	public function testGt(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` > \'value\'', DAO::toCriterion('attr', 'value', '>')->toSqlString($crit, $crit));
		}
	}

	public function testGe(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` >= \'value\'', DAO::toCriterion('attr', 'value', '>=')->toSqlString($crit, $crit));
		}
	}

	public function testLt(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` < \'value\'', DAO::toCriterion('attr', 'value', '<')->toSqlString($crit, $crit));
		}
	}

	public function testLe(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` <= \'value\'', DAO::toCriterion('attr', 'value', '<=')->toSqlString($crit, $crit));
		}
	}

	public function testLike(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` LIKE \'value\'', DAO::toCriterion('attr', 'value', 'LIKE')->toSqlString($crit, $crit));
		}
	}

	public function testIn(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` IN (\'value\')', DAO::toCriterion('attr', array('value'), 'IN')->toSqlString($crit, $crit));
		}
	}

	public function testNotIn(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals('`attr` NOT IN (\'value\')', DAO::toCriterion('attr', array('value'), 'NOT IN')->toSqlString($crit, $crit));
		}
	}

	public function testAndRestrictions(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals(
				'(`attr1` = \'value1\') AND (`attr2` != \'value2\')', 
				DAO::toRestrictions(array(
					array('attr1', 'value1'),
					array('attr2', 'value2', '!='),
				), 'and')
				->toSqlString($crit, $crit));
		}
	}

	public function testOrRestrictions(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$crit = $dao->createCriteria();
			$this->assertEquals(
				'(`attr1` = \'value1\') OR (`attr2` != \'value2\')', 
				DAO::toRestrictions(array(
					array('attr1', 'value1'),
					array('attr2', 'value2', '!='),
				), 'or')
				->toSqlString($crit, $crit));
		}
	}

	// TODO Test createOrder and createOrders
}