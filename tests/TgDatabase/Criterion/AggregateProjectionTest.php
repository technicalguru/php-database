<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;

/**
 * Tests the AggregateProjection.
 * @author ralph
 *
 */
final class AggregateProjectionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Projections::max('aName');
        $this->testSqlString('MAX(`aName`)', $expr);
    }
        
    protected function testSqlString(string $expected, Projection $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}