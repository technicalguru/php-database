<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Query;
use TgDatabase\TestHelper;

/**
 * Tests the InExpression.
 * @author ralph
 *
 */
final class InExpressionTest extends TestCase {
    
    public function testSimple(): void {
        $expr = Restrictions::in('aName', array('aValue1', 'aValue2'));
        $this->testSqlString('`aName` IN (\'aValue1\',\'aValue2\')', $expr);
    }
        
    public function testWithIgnoreCase(): void {
        $expr = Restrictions::in('aName', array('aValue1', 'aValue2'))->ignoreCase();
        $this->testSqlString('LOWER(`aName`) IN (\'avalue1\',\'avalue2\')', $expr);
    }
        
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}