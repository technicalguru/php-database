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
    
    
}