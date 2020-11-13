<?php

namespace TgDatabase;

use TgLog\Log;

class Database {

	protected $dbconfig;
	public    $con;

	public function __construct($dbconfig, \TgUtils\Auth\CredentialsProvider $provider = NULL) {
		$this->dbconfig = $dbconfig;
		$this->connect($provider);
	}

	protected function connect(\TgUtils\Auth\CredentialsProvider $provider = NULL) {
		if ($this->con == null) {
			$username = '';
			$password = '';
			if ($provider != NULL) {
				$username = $provider->getUsername();
				$password = $provider->getPassword();
			} else {
				$username = $this->dbconfig['user'];
				$password = $this->dbconfig['pass'];
			}
			$this->con = new \mysqli(
				$this->dbconfig['host'],
				$username,
				$password,
				$this->dbconfig['dbname'],
				$this->dbconfig['port']
			);
			if ($this->con->connect_errno) {
				error_log('Failed to connect to MySQL: '.$this->con->connect_errno);
			}
			$this->configureConnection();
		}
	}

	protected function configureConnection() {
		$this->con->query('SET time_zone = \'UTC\'');
		$this->con->set_charset("utf8");
	}

	public function escape($s) {
		return $this->con->real_escape_string($s);
	}

	public function quote($s) {
		return '\''.$this->escape($s).'\'';
	}

	public function quoteName($s) {
		return '`'.$s.'`';
	}

	public function query($sql) {
		$sql = $this->replaceTablePrefix($sql);
		return $this->con->query($sql);
	}

	private function logError($sql) {
		Log::error($this->error());
		Log::error($sql);
		Log::errorStackTrace(__FILE__);
	}

	public function querySingle($sql, $class = 'stdClass') {
		$sql = $this->replaceTablePrefix($sql);
		$rc  = null;
		$res = $this->query($sql);
		if ($res !== FALSE) {
			$rc = $res->fetch_object($class);
			$res->free();
		} else {
			$this->logError($sql);
		}
		return $rc;
	}

	public function queryList($sql, $class = 'stdClass') {
		$sql = $this->replaceTablePrefix($sql);
		$rc  = array();
		$res = $this->query($sql);
		if ($res !== FALSE) {
			while (($obj = $res->fetch_object($class)) !== null) {
				$rc[] = $obj;
			}
			$res->free();
		} else {
			$this->logError($sql);
		}
		return $rc;
	}

	public function insert_id() {
		return $this->con->insert_id;
	}

	public function error() {
		return $this->con->error;
	}

	public function insert($table, $fields) {
		$table = $this->replaceTablePrefix($table);
		if (is_object($fields)) $fields = get_object_vars($fields);
		$fieldNames = array_keys($fields);
		$sqlFieldNames = '`'.implode('`,`', $fieldNames).'`';
		$values = array();
		foreach ($fields AS $k => $v) {
			if ($v === NULL) $values[] = 'NULL';
			else $values[] = $this->prepareValue($v);
		}
		$sql = 'INSERT INTO '.$table.' ('.$sqlFieldNames.') VALUES ('.implode(',', $values).')';
		Log::debug($sql);
		$rc = $this->query($sql);
		if ($rc === FALSE) {
			$this->logError($sql);
		} else {
			$rc = $this->insert_id();
		}
		return $rc;
	}

	public function update($table, $fields, $where) {
		$table = $this->replaceTablePrefix($table);
		if (is_object($fields)) $fields = get_object_vars($fields);
		$values = array();
		foreach ($fields AS $k => $v) {
			$value = $v;
			if ($v === NULL) $value = 'NULL';
			else $value = $this->prepareValue($v);
			$values[] = '`'.$k.'`='.$value;
		}
		$sql = 'UPDATE '.$table.' SET '.implode(', ', $values).' WHERE '.$where;
		Log::debug($sql);
		$rc = $this->query($sql);
		if ($rc === FALSE) {
			$this->logError($sql);
		} else {
			$rc = $this->queryList('SELECT * FROM '.$table.' WHERE '.$where);
		}
		return $rc;
	}

	public function delete($table, $where) {
		$table = $this->replaceTablePrefix($table);
		$sql = 'DELETE FROM '.$table.' WHERE '.$where;
		Log::debug($sql);
		$rc = $this->query($sql);
		if ($rc === FALSE) {
			$this->logError($sql);
		}
		return $rc;
	}

	public function prepareValue($value) {
		$rc = $value;
		if (is_object($value)) {
			if (get_class($value) == 'TgUtils\\Date') $rc = $this->quote($value->toMysql(true));
			else $rc = $this->quote(json_encode($value));
		} else if (is_array($value)) {
			$rc = $this->quote(json_encode($value));
		} else if (is_numeric($value)) {
			// Nothing to do
		} else {
			$rc = $this->quote($value);
		}
		return $rc;
	}

	public function updateSingle($table, $fields, $where) {
		$rc = $this->update($table, $fields, $where);
		if (is_array($rc) && count($rc) > 0) return $rc[0];
		return $rc;
	}

	/** Replace the #__ with the tablePrefix (if configured) */
	protected function replaceTablePrefix($s) {
		$prefix = $this->dbconfig['tablePrefix'];
		if (!$prefix) $prefix = '';
		return str_replace('#__', $prefix, $s);
	}

}
