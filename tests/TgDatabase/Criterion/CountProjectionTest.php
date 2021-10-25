<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;

/**
 * Tests the CountProjection.
 * @author ralph
 *
 */
final class CountProjectionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Projections::count('aName');
        $this->testSqlString('COUNT(`aName`)', $expr);
    }
        
    public function testDistinct(): void {
        $expr = Projections::countDistinct('aName');
        $this->testSqlString('COUNT(DISTINCT `aName`)', $expr);
    }
        
    protected function testSqlString(string $expected, Projection $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}