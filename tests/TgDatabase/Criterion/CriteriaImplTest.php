<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Projections;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;
use TgDatabase\Order;

/**
 * Tests the CriteriaImpl.
 * @author ralph
 *
 */
final class CriteriaImplTest extends TestCase {
    
    public function testSimple(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $criteria->add(Restrictions::eq('aName', 'aValue'));
            $criteria->addOrder(Order::asc('anotherName'));
            $this->assertEquals('SELECT * FROM `dual` WHERE (`aName` = \'aValue\') ORDER BY `anotherName`', $criteria->toSqlString());
        }
    }
    
    public function testLimit(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $criteria->setFirstResult(5);
            $criteria->setMaxResults(20);
            $this->assertEquals('SELECT * FROM `dual` LIMIT 20 OFFSET 5', $criteria->toSqlString());
        }
    }
    
    public function testProjection(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $criteria->setProjection(Projections::rowCount());
            $this->assertEquals('SELECT COUNT(*) FROM `dual`', $criteria->toSqlString());
        }
    }
    
    public function testJoin(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual', NULL, 'a');
            $criteria->createCriteria('otherTable', 'b', Restrictions::eqProperty(array('a', 'details'), array('b', 'uid')));
            $this->assertEquals('SELECT `a`.* FROM `dual` AS `a` INNER JOIN `otherTable` AS `b` ON `a`.`details` = `b`.`uid`', $criteria->toSqlString());
        }
    }
    
    public function testQuoteNameSimple(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('`aName`', $criteria->quoteName(NULL, 'aName'));
        }
    }
    
    public function testQuoteNameSimple2(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('`aName`', $criteria->quoteName('aName'));
        }
    }
    
    public function testQuoteNameCriteriaAlias(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('`a`.`aName`', $criteria->quoteName('a', 'aName'));
        }
    }
    
    public function testQuoteNameExplicitAlias(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('`b`.`aName`', $criteria->quoteName('a', array('b', 'aName')));
        }
    }
    
    public function testQuoteNameExplicitAlias2(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('`b`.`aName`', $criteria->quoteName(array('b', 'aName')));
        }
    }
    
    public function testPrepareValueString(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('\'aString\'', $criteria->prepareValue('aString', FALSE));
        }
    }
    
    public function testPrepareValueStringIgnoreCase(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals('\'astring\'', $criteria->prepareValue('aString', TRUE));
        }
    }
    
    public function testPrepareValueNoString(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals(13, $criteria->prepareValue(13, FALSE));
        }
    }
    
    public function testPrepareValueNoStringIgnoreCase(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $criteria = new CriteriaImpl($database, 'dual');
            $this->assertEquals(13, $criteria->prepareValue(13, TRUE));
        }
    }
    
    
}