<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Restrictions class.
 * @author ralph
 *
 */
final class RestrictionsTest extends TestCase {
    
    public function testEq(): void {
        $expr = Restrictions::eq('aName', 'aValue');
        $this->testSqlString('`aName` = \'aValue\'', $expr);
    }
        
    public function testEqOrNUll1(): void {
        $expr = Restrictions::eqOrNull('aName', 'aValue');
        $this->testSqlString('`aName` = \'aValue\'', $expr);
    }
        
    public function testEqOrNull2(): void {
        $expr = Restrictions::eqOrNull('aName', NULL);
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    public function testNe(): void {
        $expr = Restrictions::ne('aName', 'aValue');
        $this->testSqlString('`aName` != \'aValue\'', $expr);
    }
        
    public function testNeOrNull1(): void {
        $expr = Restrictions::neOrNull('aName', 'aValue');
        $this->testSqlString('`aName` != \'aValue\'', $expr);
    }
        
    public function testNeOrNull2(): void {
        $expr = Restrictions::neOrNull('aName', NULL);
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    public function testLike(): void {
        $expr = Restrictions::like('aName', 'a%Value');
        $this->testSqlString('`aName` LIKE \'a%Value\'', $expr);
    }
        
    public function testGt(): void {
        $expr = Restrictions::gt('aName', 'aValue');
        $this->testSqlString('`aName` > \'aValue\'', $expr);
    }
        
    public function testGe(): void {
        $expr = Restrictions::ge('aName', 'aValue');
        $this->testSqlString('`aName` >= \'aValue\'', $expr);
    }
        
    public function testlt(): void {
        $expr = Restrictions::lt('aName', 'aValue');
        $this->testSqlString('`aName` < \'aValue\'', $expr);
    }
        
    public function testle(): void {
        $expr = Restrictions::le('aName', 'aValue');
        $this->testSqlString('`aName` <= \'aValue\'', $expr);
    }
        
    public function testBetween(): void {
        $expr = Restrictions::between('aName', 'aValue1', 'aValue2');
        $this->testSqlString('`aName` BETWEEN \'aValue1\' AND \'aValue2\'', $expr);
    }
        
    public function testBetweenIgnoreCase(): void {
        $expr = Restrictions::between('aName', 'aValue1', 'aValue2')->ignoreCase();
        $this->testSqlString('LOWER(`aName`) BETWEEN \'avalue1\' AND \'avalue2\'', $expr);
    }
        
    public function testIsNull(): void {
        $expr = Restrictions::isNull('aName');
        $this->testSqlString('`aName` IS NULL', $expr);
    }
        
    public function testIsNotNull(): void {
        $expr = Restrictions::isNotNull('aName');
        $this->testSqlString('`aName` IS NOT NULL', $expr);
    }
        
    public function testIn(): void {
        $expr = Restrictions::in('aName', array('aValue1','aValue2'));
        $this->testSqlString('`aName` IN (\'aValue1\',\'aValue2\')', $expr);
    }
        
    public function testNotIn(): void {
        $expr = Restrictions::notIn('aName', array('aValue1','aValue2'));
        $this->testSqlString('`aName` NOT IN (\'aValue1\',\'aValue2\')', $expr);
    }
        
    public function testAnd(): void {
        $expr = Restrictions::and(Restrictions::eq('aName1','aValue1'), Restrictions::eq('aName2','aValue2'));
        $this->testSqlString('(`aName1` = \'aValue1\') AND (`aName2` = \'aValue2\')', $expr);
    }
        
    public function testOr(): void {
        $expr = Restrictions::or(Restrictions::eq('aName1','aValue1'), Restrictions::eq('aName2','aValue2'));
        $this->testSqlString('(`aName1` = \'aValue1\') OR (`aName2` = \'aValue2\')', $expr);
    }
        
    public function testEqProperty(): void {
        $expr = Restrictions::eqProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` = `aName2`', $expr);
    }
        
    public function testNeProperty(): void {
        $expr = Restrictions::neProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` != `aName2`', $expr);
    }
        
    public function testGtProperty(): void {
        $expr = Restrictions::gtProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` > `aName2`', $expr);
    }
        
    public function testGeProperty(): void {
        $expr = Restrictions::geProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` >= `aName2`', $expr);
    }
        
    public function testLtProperty(): void {
        $expr = Restrictions::ltProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` < `aName2`', $expr);
    }
        
    public function testLeProperty(): void {
        $expr = Restrictions::leProperty('aName1', 'aName2');
        $this->testSqlString('`aName1` <= `aName2`', $expr);
    }
        
    protected function testSqlString(string $expected, Criterion $expr, $alias = NULL): void {
        $query = TestHelper::createQuery(NULL, NULL, $alias);
        if ($query != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($query,$query));
        }
    }
}