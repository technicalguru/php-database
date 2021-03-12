<?php declare(strict_types=1);

namespace TgDatabase\Criterion;

use PHPUnit\Framework\TestCase;
use TgDatabase\Restrictions;
use TgDatabase\Criterion;
use TgDatabase\Criteria;
use TgDatabase\Impl\CriteriaImpl;
use TgDatabase\Database;

/**
 * Tests the SimpleExpression.
 * @author ralph
 *
 */
final class SimpleExpressionTest extends TestCase {
    
    protected ?Criteria $criteria = NULL;
    
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
        $criteria = $this->getCriteria($alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
    
    protected function getCriteria($alias): ?Criteria {
        if (getenv('DB_TEST_HOST') != NULL) {
            if ($this->criteria == NULL) {
                $config = array(
                    'host'        => getenv('DB_TEST_HOST'),
                    'port'        => intval(getenv('DB_TEST_PORT')),
                    'dbname'      => getenv('DB_TEST_NAME'),
                    'tablePrefix' => getenv('DB_TEST_PREFIX'),
                    'user'        => getenv('DB_TEST_USER'),
                    'pass'        => getenv('DB_TEST_PASS'),
                );
                $this->database = new Database($config);
                $this->criteria = new CriteriaImpl($this->database, 'dual', NULL, $alias);
            }
        }
        return $this->criteria;
    }
}