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
        
    public function testQuoteNameSimple(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('`aName`', $database->quoteName(NULL, 'aName'));
        }
    }
    
    public function testQuoteNameSimple2(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('`aName`', $database->quoteName('aName'));
        }
    }
    
    public function testQuoteNameQueryAlias(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('`a`.`aName`', $database->quoteName('a', 'aName'));
        }
    }
    
    public function testQuoteNameExplicitAlias(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('`b`.`aName`', $database->quoteName('a', array('b', 'aName')));
        }
    }
    
    public function testQuoteNameExplicitAlias2(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            $this->assertEquals('`b`.`aName`', $database->quoteName(array('b', 'aName')));
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

    public function testUpdateCompatible(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            if (TestHelper::createTestTable()) try {
                $tableName = TestHelper::getTestTable();
                $fields = array(
                    'attr1' => 'value11_updated',
                );
                $where = 'uid=1';
                $rc = $database->update($tableName, $fields, $where);
                $this->assertNotNull($rc);
                $this->assertEquals(1, count($rc));
                foreach ($fields AS $name => $value) {
                    $this->assertEquals($value, $rc[0]->$name);
                }
            } finally {
                TestHelper::deleteTestTable();
            }
        }
    }

    public function testUpdateSingleCompatible(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            if (TestHelper::createTestTable()) try {
                $tableName = TestHelper::getTestTable();
                $fields = array(
                    'attr2' => 'value22_updated',
                );
                $where = 'uid=2';
                $rc = $database->updateSingle($tableName, $fields, $where);
                $this->assertNotNull($rc);
                foreach ($fields AS $name => $value) {
                    $this->assertEquals($value, $rc->$name);
                }
            } finally {
                TestHelper::deleteTestTable();
            }
        }
    }

    public function testDeleteCompatible(): void {
        $database = TestHelper::getDatabase();
        if ($database != NULL) {
            if (TestHelper::createTestTable()) try {
                $tableName = TestHelper::getTestTable();
                $where = 'uid=1';
                $rc = $database->delete($tableName, $where);
                $this->assertTrue($rc !== FALSE);
                $rc = $database->queryList('SELECT * FROM `'.$tableName.'`');
                $this->assertNotNull($rc);
                $this->assertEquals(9, count($rc));
            } finally {
                TestHelper::deleteTestTable();
            }
        }
    }


}