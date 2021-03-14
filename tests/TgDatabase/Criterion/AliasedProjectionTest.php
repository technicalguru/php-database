<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;

/**
 * Tests the AliasedProjection.
 * @author ralph
 *
 */
final class AliasedProjectionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Projections::alias(Projections::rowCount(), 'cnt');
        $this->testSqlString('COUNT(*) AS `cnt`', $expr);
    }
        
    protected function testSqlString(string $expected, Projection $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}