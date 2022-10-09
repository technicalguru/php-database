<?php

namespace TgDatabase;

/**
 * A simple class to keep all DAOs in one place
 *
 * @author ralph
 *        
 */
class DataModel {

    private $database;

    private $models;

	private $daoFactory;

    /**
     * Constructor.
     * @param Database $database - the database instance
     */
    public function __construct($database, $daoFactory = NULL) {
        $this->database   = $database;
		$this->daoFactory = $daoFactory;
        $this->models     = array();
        $this->init($database);
    }

	/**
	 * Returns the DAO factory object.
	 */
	public function getDaoFactory() {
		return $this->daoFactory;
	}

	/**
	 * Sets the DAO factory object
	 */
	public function setDaoFactory($daoFactory) {
		$this->daoFactory = $daoFactory;
	}

    /**
     * Initializes all DAOs.
     * <p>This method does nothing. Descendants shall override here and create and register their DAOs.</p>
     * @param Database $database - the database object
     */
    protected function init($database) {
    }

    /**
     * Returns a DAO registered under a certain name.
     * @param String name - name of model
     * return DAO the DAO registered or NULL
     */
    public function get($name) {
		if (!isset($this->models[$name]) && ($this->daoFactory != NULL)) {
			$this->register($name, $this->daoFactory->createDao($name));
		} 
        return isset($this->models[$name]) ? $this->models[$name] : NULL;
    }

    /**
     * Returns the database object.
     * @return Database the database object.
     */
    public function getDatabase() {
        return $this->database;
    }

    /**
     * Registers a DAO under a name.
     * @param string $name - the name of the model
     * @param DAO $dao - the DAO to be registered
     */
    public function register($name, $dao) {
        $this->models[$name] = $dao;
    }

	/**
	 * Performs a check of each DAO whether underlying tables exists and creates them if required.
	 * @return object with the result(s):
	 * {
	 *   "tableChecks" : {
	 *      "<dao-registered-name>" : TRUE|FALSE,
	 *      ...
	 *   },
	 *   "success" : TRUE|FALSE
	 * }
	 */
	public function checkTables() {
		$rc = new \stdClass;
		$rc->tableChecks = new \stdClass;
		$rc->success     = TRUE;
		foreach ($this->models AS $name => $dao) {
			if ($dao != NULL) {
				$rc->tableChecks->$name = $dao->checkTable();
				if (!$rc->tableChecks->$name) $rc->success = FALSE;
			}
		}
		return $rc;
	}
}

