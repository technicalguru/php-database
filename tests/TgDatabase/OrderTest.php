<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Order class.
 * @author ralph
 *
 */
final class OrderTest extends TestCase {
    
    public function testAsc(): void {
        $expr = Order::asc('aName');
        $this->testSqlString('`aName`', $expr);
    }
        
    public function testAscIgnoreCase(): void {
        $expr = Order::asc('aName')->ignoreCase();
        $this->testSqlString('LOWER(`aName`)', $expr);
    }
                
    public function testDesc(): void {
        $expr = Order::desc('aName');
        $this->testSqlString('`aName` DESC', $expr);
    }
        
    public function testDescIgnoreCase(): void {
        $expr = Order::desc('aName')->ignoreCase();
        $this->testSqlString('LOWER(`aName`) DESC', $expr);
    }
                
    protected function testSqlString(string $expected, Order $expr, $alias = NULL): void {
        $criteria = TestHelper::createCriteria(NULL, NULL, $alias);
        if ($criteria != NULL) {
            $this->assertEquals($expected, $expr->toSqlString($criteria,$criteria));
        }
    }
}