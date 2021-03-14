<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
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
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}