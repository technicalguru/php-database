<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Query;
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
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}