<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
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
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
    
}