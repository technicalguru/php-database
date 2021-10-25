<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Query;
use TgDatabase\TestHelper;

/**
 * Tests the NotNullExpression.
 * @author ralph
 *
 */
final class NotNullExpressionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Restrictions::isNotNull('aName');
        $this->testSqlString('`aName` IS NOT NULL', $expr);
    }
        
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}