<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
use TgDatabase\TestHelper;

/**
 * Tests the NotInExpression.
 * @author ralph
 *
 */
final class NotInExpressionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Restrictions::notIn('aName', array('aValue1', 'aValue2'));
        $this->testSqlString('`aName` NOT IN (\'aValue1\',\'aValue2\')', $expr);
    }
        
    public function testWithIgnoreCase(): void {
        $expr = Restrictions::notIn('aName', array('aValue1', 'aValue2'))->ignoreCase();
        $this->testSqlString('LOWER(`aName`) NOT IN (\'avalue1\',\'avalue2\')', $expr);
    }
        
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}