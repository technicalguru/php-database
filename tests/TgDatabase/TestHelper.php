<?php declare(strict_types=1);

namespace TgDatabase;

use TgDatabase\Criteria;
use TgDatabase\Impl\CriteriaImpl;
use TgDatabase\Database;

/**
 * Abstract test to ease database, DAO and Criteria creation.
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

    public static function createCriteria($tableName = 'dual', $modelClass = NULL, $alias = NULL): ?Criteria {
        if (self::hasTestDatabase()) {
            return new CriteriaImpl(self::getDatabase(), $tableName, $modelClass, $alias);
        }
        return NULL;
    }
    
    public static function hasTestDatabase(): bool {
        return getenv('DB_TEST_HOST') != NULL;
    }
}

