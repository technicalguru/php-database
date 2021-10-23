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
    
    public function testProjection(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $query->setProjection(Projections::rowCount());
            $this->assertEquals('SELECT COUNT(*) FROM `dual`', $query->getSelectSql());
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
    
    public function testQuoteNameSimple(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('`aName`', $query->quoteName(NULL, 'aName'));
        }
    }
    
    public function testQuoteNameSimple2(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('`aName`', $query->quoteName('aName'));
        }
    }
    
    public function testQuoteNameQueryAlias(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('`a`.`aName`', $query->quoteName('a', 'aName'));
        }
    }
    
    public function testQuoteNameExplicitAlias(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('`b`.`aName`', $query->quoteName('a', array('b', 'aName')));
        }
    }
    
    public function testQuoteNameExplicitAlias2(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = new QueryImpl($database, 'dual');
            $this->assertEquals('`b`.`aName`', $query->quoteName(array('b', 'aName')));
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
    
    // TODO We need to test update and delete SQL
}