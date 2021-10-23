<?php declare(strict_types=1);

namespace TgDatabase;

use PHPUnit\Framework\TestCase;
use TgUtils\Date;

/**
 * Tests the Database class.
 * @author ralph
 *
 */
final class DatabaseTest extends TestCase {
    
    public function testQuote(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('\'aString\'', $database->quote('aString'));
        }
    }
        
    public function testQuoteName(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('`aString`', $database->quoteName('aString'));
        }
    }
                
    public function testPrepareValueString(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('\'aString\'', $database->prepareValue('aString'));
        }
    }

    public function testPrepareValueInt(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals(12, $database->prepareValue(12));
        }
    }
    
    public function testPrepareValueFloat(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals(12.3, $database->prepareValue(12.3));
        }
    }
    
    public function testPrepareValueDate(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('\'1970-01-01 00:00:00\'', $database->prepareValue(new Date(0, 'UTC')));
        }
    }
    
    public function testPrepareValueObject(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $obj = new \stdClass;
            $obj->attr = 'value';
            $this->assertEquals('\'{\\"attr\\":\\"value\\"}\'', $database->prepareValue($obj));
        }
    }
    
    public function testReplaceTablePrefix(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('phpunittest_table', $database->replaceTablePrefix('#__table'));
        }
    }
      
    public function testCreateQuery(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $query = $database->createQuery('aTable');
            $this->assertEquals('SELECT * FROM `aTable`', $query->getSelectSql());
        }
    }
}