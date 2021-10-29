<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Projections;
use TgDatabase\Criterion;
use TgDatabase\Query;
use TgDatabase\TestHelper;
use TgDatabase\Order;

/**
 * Tests the QueryImpl.
 * @author ralph
 *
 */
final class QueryImplTest extends TestCase {
    
    public function testSimple(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->add(Restrictions::eq('aName', 'aValue'));
            $query->addOrder(Order::asc('anotherName'));
            $this->assertEquals('SELECT * FROM `dual` WHERE (`aName` = \'aValue\') ORDER BY `anotherName`', $query->getSelectSql());
        }
    }
    
    public function testLimit(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->setFirstResult(5);
            $query->setMaxResults(20);
            $this->assertEquals('SELECT * FROM `dual` LIMIT 20 OFFSET 5', $query->getSelectSql());
        }
    }
    
    public function testSimpleProjection(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->setProjection(Projections::rowCount('cnt'));
            $this->assertEquals('SELECT COUNT(*) AS `cnt` FROM `dual`', $query->getSelectSql());
        }
    }
    
    public function testSetColumns(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->setColumns(Projections::property('column1'), Projections::property('column2'));
            $this->assertEquals('SELECT `column1`, `column2` FROM `dual`', $query->getSelectSql());
        }
    }
    
    public function testCombineColumns(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->setColumns(Projections::properties('column1', 'column2'));
            $this->assertEquals('SELECT `column1`, `column2` FROM `dual`', $query->getSelectSql());
        }
    }
    
    public function testAddColumns(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->addColumns(Projections::property('column1'))->addColumns(Projections::property('column2'));
            $this->assertEquals('SELECT `column1`, `column2` FROM `dual`', $query->getSelectSql());
        }
    }
    
    public function testJoin(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual', NULL, 'a');
            $query->createJoinedQuery('otherTable', 'b', Restrictions::eqProperty(array('a', 'details'), array('b', 'uid')));
            $this->assertEquals('SELECT `a`.* FROM `dual` AS `a` INNER JOIN `otherTable` AS `b` ON `a`.`details` = `b`.`uid`', $query->getSelectSql());
        }
    }
    
    public function testPrepareValueString(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('\'aString\'', $query->prepareValue('aString', FALSE));
        }
    }
    
    public function testPrepareValueStringIgnoreCase(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('\'astring\'', $query->prepareValue('aString', TRUE));
        }
    }
    
    public function testPrepareValueNoString(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals(13, $query->prepareValue(13, FALSE));
        }
    }
    
    public function testPrepareValueNoStringIgnoreCase(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals(13, $query->prepareValue(13, TRUE));
        }
    }
    
    public function testUpdateSql(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $fields = array(
                'attr1' => 'value1',
                'attr2' => 2,
            );
            $this->assertEquals("UPDATE `dual` SET `attr1`='value1', `attr2`=2", $query->getUpdateSql($fields));
        }
    }
    
    public function testUpdateWhereSql(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->add(Restrictions::eq('attr3', 'value3'));
            $fields = array(
                'attr1' => 'value1',
                'attr2' => 2,
            );
            $this->assertEquals("UPDATE `dual` SET `attr1`='value1', `attr2`=2 WHERE (`attr3` = 'value3')", $query->getUpdateSql($fields));
        }
    }
    
    public function testDeleteSql(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals("DELETE FROM `dual`", $query->getDeleteSql());
        }
    }
    
    public function testDeleteWhereSql(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->add(Restrictions::eq('attr3', 'value3'));
            $this->assertEquals("DELETE FROM `dual` WHERE (`attr3` = 'value3')", $query->getDeleteSql());
        }
    }

    public function testGroupBySql(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query
				->addColumns(Projections::property('attr1'), Projections::rowCount('cnt'))
				->add(Restrictions::eq('attr3', 'value3'))
				->addGroupBy(Projections::property('attr1'));
            $this->assertEquals("SELECT `attr1`, COUNT(*) AS `cnt` FROM `dual` GROUP BY `attr1` WHERE (`attr3` = 'value3')", $query->getSelectSql());
        }
    }

    public function testHavingSql(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query
				->addColumns(Projections::property('attr1'), Projections::rowCount('cnt'))
				->add(Restrictions::eq('attr3', 'value3'))
				->addGroupBy(Projections::property('attr1'))
				->addHaving(Restrictions::eq('attr1', 'value1'));
            $this->assertEquals("SELECT `attr1`, COUNT(*) AS `cnt` FROM `dual` GROUP BY `attr1` HAVING (`attr1` = 'value1') WHERE (`attr3` = 'value3')", $query->getSelectSql());
        }
    }

    public function testCount(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            if (TestHelper::createTestTable()) try {
                $query = new QueryImpl($database, TestHelper::getTestTable());
                $this->assertEquals(10, $query->count());
            } finally {
                TestHelper::deleteTestTable();
            }
        }
    }
}
