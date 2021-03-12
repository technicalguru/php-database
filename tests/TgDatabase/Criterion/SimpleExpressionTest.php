<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;

/**
 * Tests the SimpleExpression.
 * @author ralph
 *
 */
final class SimpleExpressionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Restrictions::eq('aName', 'aValue');
        $this->testSqlString('`aName` = \'aValue\'', $expr);
    }
    
    public function testSimpleWithExplicitAlias(): void {
        $expr = Restrictions::eq(array('a', 'aName'), 'aValue');
        $this->testSqlString('`a`.`aName` = \'aValue\'', $expr);
    }
    
    public function testSimpleWithImplicitAlias(): void {
        $expr = Restrictions::eq('aName', 'aValue');
        $this->testSqlString('`a`.`aName` = \'aValue\'', $expr, 'a');
    }
    
    public function testIgnoreCase(): void {
        $expr = Restrictions::eq('aName', 'aValue')->ignoreCase();
        $this->testSqlString('LOWER(`aName`) = \'avalue\'', $expr);
    }
    
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
    
}