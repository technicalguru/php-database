<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Projections;
use TgDatabase\Projection;
use TgDatabase\Query;
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
        
    protected function testSqlString(string $expected, $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}