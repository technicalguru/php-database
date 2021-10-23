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
			$query = $dao->createQuery();
			$this->assertEquals('`attr` = \'value\'', DAO::toCriterion('attr', 'value')->toSqlString($query, $query));
		}
	}

	public function testArgumentArray(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` = \'value\'', DAO::toCriterion(array('attr', 'value'))->toSqlString($query, $query));
		}
	}

	public function testOperatorArgument(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` = \'value\'', DAO::toCriterion(array('attr', 'value', '='))->toSqlString($query, $query));
		}
	}

	public function testNull(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` IS NULL', DAO::toCriterion('attr', NULL)->toSqlString($query, $query));
		}
	}

	public function testNotNull(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` IS NOT NULL', DAO::toCriterion('attr', NULL, '!=')->toSqlString($query, $query));
		}
	}

	public function testNe(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` != \'value\'', DAO::toCriterion('attr', 'value', '!=')->toSqlString($query, $query));
		}
	}

	public function testGt(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` > \'value\'', DAO::toCriterion('attr', 'value', '>')->toSqlString($query, $query));
		}
	}

	public function testGe(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` >= \'value\'', DAO::toCriterion('attr', 'value', '>=')->toSqlString($query, $query));
		}
	}

	public function testLt(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` < \'value\'', DAO::toCriterion('attr', 'value', '<')->toSqlString($query, $query));
		}
	}

	public function testLe(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` <= \'value\'', DAO::toCriterion('attr', 'value', '<=')->toSqlString($query, $query));
		}
	}

	public function testLike(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` LIKE \'value\'', DAO::toCriterion('attr', 'value', 'LIKE')->toSqlString($query, $query));
		}
	}

	public function testIn(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` IN (\'value\')', DAO::toCriterion('attr', array('value'), 'IN')->toSqlString($query, $query));
		}
	}

	public function testNotIn(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals('`attr` NOT IN (\'value\')', DAO::toCriterion('attr', array('value'), 'NOT IN')->toSqlString($query, $query));
		}
	}

	public function testAndRestrictions(): void {
		$dao = TestHelper::getDao();
        if ($dao != NULL) {
			$query = $dao->createQuery();
			$this->assertEquals(
				'(`attr1` = \'value1\') AND (`attr2` != \'value2\')', 
				DAO::toRestrictions(array(
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
				DAO::toRestrictions(array(
					array('attr1', 'value1'),
					array('attr2', 'value2', '!='),
				), 'or')
				->toSqlString($query, $query));
		}
	}

	// TODO Test createOrder and createOrders
}