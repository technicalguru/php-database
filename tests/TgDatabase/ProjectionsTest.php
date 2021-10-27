<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Projections class.
 * @author ralph
 *
 */
final class ProjectionsTest extends TestCase {
    
    public function testDistinct(): void {
        $expr = Projections::distinct(Projections::max('aName'));
        $this->testSqlString('DISTINCT MAX(`aName`)', $expr);
    }
        
    public function testRowCount(): void {
        $expr = Projections::rowCount();
        $this->testSqlString('COUNT(*)', $expr);
    }
        
    public function testCount(): void {
        $expr = Projections::count('aName');
        $this->testSqlString('COUNT(`aName`)', $expr);
    }
        
    public function testCountDistinct(): void {
        $expr = Projections::countDistinct('aName');
        $this->testSqlString('COUNT(DISTINCT `aName`)', $expr);
    }
        
    public function testAvg(): void {
        $expr = Projections::avg('aName');
        $this->testSqlString('AVG(`aName`)', $expr);
    }
        
    public function testMax(): void {
        $expr = Projections::max('aName');
        $this->testSqlString('MAX(`aName`)', $expr);
    }
        
    public function testMin(): void {
        $expr = Projections::min('aName');
        $this->testSqlString('MIN(`aName`)', $expr);
    }
        
    public function testSum(): void {
        $expr = Projections::sum('aName');
        $this->testSqlString('SUM(`aName`)', $expr);
    }
        
    public function testAlias(): void {
        $expr = Projections::alias(Projections::max('aName'), 'anotherName');
        $this->testSqlString('MAX(`aName`) AS `anotherName`', $expr);
    }
        
    protected function testSqlString(string $expected, SelectComponent $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}