<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Query;
use TgDatabase\TestHelper;

/**
 * Tests the NullExpression.
 * @author ralph
 *
 */
final class NullExpressionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Restrictions::isNull('aName');
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}