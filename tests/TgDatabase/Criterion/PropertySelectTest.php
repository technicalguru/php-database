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
final class PropertySelectTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Projections::property('aName');
        $this->testSqlString('`aName`', $expr);
    }
        
    public function testAliased(): void {
        $expr = Projections::property('aName', 'anAlias');
        $this->testSqlString('`aName` AS `anAlias`', $expr);
    }
        
    protected function testSqlString(string $expected, $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}