<?php

namespace TgDatabase;

interface DaoFactory {

	/**
	 * Creates a DAO of the given name.
	 */
	public function createDao($name);

}
