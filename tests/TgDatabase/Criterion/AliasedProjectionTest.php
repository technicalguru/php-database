<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Query;
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
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}