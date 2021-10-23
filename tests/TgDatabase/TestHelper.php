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
}

