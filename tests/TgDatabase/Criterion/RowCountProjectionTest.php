<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;

/**
 * Tests the RowCountProjection.
 * @author ralph
 *
 */
final class RowCountProjectionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Projections::rowCount();
        $this->testSqlString('COUNT(*)', $expr);
    }
        
    protected function testSqlString(string $expected, Projection $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}