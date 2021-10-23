<?php declare(strict_types=1);

namespace TgDatabase;

use TgDatabase\Query;
use TgDatabase\Criterion\QueryImpl;
use TgDatabase\Database;

/**
 * Abstract test to ease database, DAO and Query creation.
 * @author ralph
 *        
 */
class TestHelper {

    protected static ?Database $database = NULL;
    protected static $TEST_TABLE_NAME = NULL;

    public static function getDatabase(): ?Database {
        if (self::hasTestDatabase()) {
            if (self::$database == NULL) {
                $config = array(
                    'host'        => getenv('DB_TEST_HOST'),
                    'port'        => intval(getenv('DB_TEST_PORT')),
                    'dbname'      => getenv('DB_TEST_NAME'),
                    'tablePrefix' => getenv('DB_TEST_PREFIX'),
                    'user'        => getenv('DB_TEST_USER'),
                    'pass'        => getenv('DB_TEST_PASS'),
                );
                self::$database = new Database($config);
            }
        }
        return self::$database;
    }

    public static function createQuery($tableName = 'dual', $modelClass = NULL, $alias = NULL): ?Query {
        if (self::hasTestDatabase()) {
            return new QueryImpl(self::getDatabase(), $tableName, $modelClass, $alias);
        }
        return NULL;
    }
    
    public static function hasTestDatabase(): bool {
        return getenv('DB_TEST_HOST') != NULL;
    }

    public static function getDao($tableName = 'dual', $modelClass = 'stdClass'): ?DAO {
		if (self::hasTestDatabase()) {
            return new DAO(self::getDatabase(), $tableName, $modelClass);
        }
        return NULL;
    }

    public static function getTestTable() {
        if (self::$TEST_TABLE_NAME == NULL) {
            self::$TEST_TABLE_NAME = 'PHP_TEST_TABLE_'.time();
        }
        return self::$TEST_TABLE_NAME;
    }

    public static function createTestTable() {
        $database = self::getDatabase();
        if ($database != NULL) {
            $tableName = self::getTestTable();
            $sql = 'CREATE TABLE `'.$tableName.'` ('.
                '`uid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,'.
                '`attr1` VARCHAR(20) NOT NULL,'.
                '`attr2` VARCHAR(20) NOT NULL,'.
                '`attr3` VARCHAR(20) NOT NULL,'.
                'PRIMARY KEY(`uid`)'.
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin';
            if ($database->query($sql) === FALSE) {
                echo "$sql => ".$database->error()."\n";
                return FALSE;
            }
            for ($i=1; $i<11; $i++) {
                $sql = 'INSERT INTO `'.$tableName.'` (`attr1`,`attr2`,`attr3`) VALUES (\'value'.$i.'1\',\'value'.$i.'2\',\'value'.$i.'3\')';
                $rc = $database->query($sql);
                if ($rc === FALSE) echo "$sql => ".$database->error()."\n";
            }
            return TRUE;
        }
        return FALSE;
    }

    public static function deleteTestTable() {
        $database = self::getDatabase();
        if ($database != NULL) {
            $tableName = self::getTestTable();
            $sql = 'DROP TABLE `'.$tableName.'`';
            if ($database->query($sql) !== FALSE) return TRUE;
            echo "$sql => ".$database->error()."\n";
        }
        return FALSE;
    }
}

