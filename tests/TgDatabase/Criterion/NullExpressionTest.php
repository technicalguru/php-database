<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
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
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}