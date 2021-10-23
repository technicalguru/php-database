<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Query;
use TgDatabase\TestHelper;

/**
 * Tests the PropertyExpression.
 * @author ralph
 *
 */
final class PropertyExpressionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Restrictions::eqProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` = `aName2`', $expr);
    }
    
    public function testIgnoreCase(): void {
        $expr = Restrictions::eqProperty('aName1', 'aName2')->ignoreCase();
        $this->testSqlString('LOWER(`aName1`) = LOWER(`aName2`)', $expr);
    }
    
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
    
}