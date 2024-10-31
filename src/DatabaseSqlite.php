<?php

namespace RestInPeace;

class DatabaseSqlite extends Database {
	/** @var string $database The path to the SQLite database file */
	public $database;
	/** @var string $username The username for the database connection */
	public $username;
	/** @var string $password The password for the database connection */
	public $password;
	/** @var string $host The hostname for the SQLite database connection */
	public $host;
	/** @var int|null The port number for the SQLite database connection. */
	public $port;
	/** @var array $excluded_analysis_keys List of keys to be excluded from analysis */
	static public $excluded_analysis_keys = ['rootpage', 'sql', 'tbl_name', 'type'];
	/**
	 * Constructor for the DatabaseSqlite class.
	 *
	 * Initializes a new instance of the DatabaseSqlite class with the specified database file.
	 *
	 * @param string $database The name of the SQLite database file. Default is 'db.sqlite'.
	 */
	public function __construct($database = 'db.sqlite') {
		$this->database = RestInPeace::database_path($database);
	}
	/**
	 * Creates a new PDO instance with the given options.
	 *
	 * @param array $options An array of options to configure the PDO instance.
	 * @return \PDO The newly created PDO instance.
	 */
	function newPDO($options = []) {
		$options = self::$connectionOptions + [
			\PDO::ATTR_TIMEOUT => 3,
		] + $options;
		$dbPath = $this->database;
		
		if (empty($dbPath)) {
			throw new \Exception("Database not found");
		}
		$dsn = "sqlite:" . $dbPath;
		$pdo = new \PDO($dsn, null, null, $options);
		// Directly from ArrestDB
		$pragmas = [
			'automatic_index' => 'ON',
			'cache_size' => '8192',
			'foreign_keys' => 'ON',
			'journal_size_limit' => '67110000',
			'locking_mode' => 'NORMAL',
			'page_size' => '4096',
			'recursive_triggers' => 'ON',
			'secure_delete' => 'ON',
			'synchronous' => 'NORMAL',
			'temp_store' => 'MEMORY',
			'journal_mode' => 'WAL',
			'wal_autocheckpoint' => '4096',
		];
		if (strncasecmp(PHP_OS, 'WIN', 3) !== 0) {
			$memory = 131072;

			if (($page = intval(shell_exec('getconf PAGESIZE'))) > 0) {
				$pragmas['page_size'] = $page;
			}

			if (is_readable('/proc/meminfo')) {
				if (is_resource($handle = fopen('/proc/meminfo', 'rb'))) {
					while (($line = fgets($handle, 1024)) !== false) {
						if (sscanf($line, 'MemTotal: %d kB', $memory) == 1) {
							$memory = round($memory / 131072) * 131072;
							break;
						}
					}

					fclose($handle);
				}
			}

			$pragmas['cache_size'] = intval($memory * 0.25 / ($pragmas['page_size'] / 1024));
			$pragmas['wal_autocheckpoint'] = $pragmas['cache_size'] / 2;
		}

		foreach ($pragmas as $key => $value) {
			$pdo->exec(sprintf('PRAGMA %s=%s;', $key, $value));
		}
		return $pdo;
	}
	/**
	 * Creates a new instance of the class from a configuration.
	 *
	 * This static method initializes the class using configuration settings.
	 *
	 * @return self Returns an instance of the class.
	 */
	static function fromConfig() {
		return new static(Config::get('DB_DATABASE', 'db.sqlite'));
	}
	/**
	 * Retrieves a list of all tables in the SQLite database.
	 *
	 * @return Table[] An array of Table objects
	 */
	public function getTables() {
		$query = "SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name";
		$tableSchemas = $this->execute($query);
		$tableNames = array_map(fn($item) => $item['name'], $tableSchemas);
		$tables = array_map(function ($tableName) {
			$table = new Table($this, $tableName);
			$table->columns = $this->getColumns($tableName);
			$table->indexes = $this->getIndexes($tableName);
			$table->primary_key = $this->getPrimaryKey($tableName);
			$table->foreign_keys = $this->getForeignKeys($tableName);
			if (!$table->isValid()) {
				return false;
			}
			return $table;
		}, $tableNames);
		$tables = array_combine($tableNames, $tables);
		$tables = array_filter($tables);
		return $tables;
	}
	/**
	 * Retrieves a list of views from the SQLite database.
	 *
	 * @return View[] An array containing the names of the views in the database.
	 */
	public function getViews() {
		//NOT TESTED RECENTLY
		$query = "SELECT name FROM sqlite_master WHERE type = 'view' ORDER BY name";
		$viewSchemas = $this->execute($query);
		$viewNames = array_map(fn($schema) => $schema['name'], $viewSchemas);
		$views = array_map(function($viewName) {
			$view = new View($this, $viewName);
			$view->columns = $this->getColumns($view->name);
			// $view->primary_key = $this->getPrimaryKey($view->name);
		}, $viewNames);
	
		$views = array_combine($viewNames, $views);
		$views = array_filter($views, fn($view) => $view->isValid());
		return $views;
	}
	public function zzgetViews() {
		$query = "SELECT * FROM sqlite_master WHERE type='view' ORDER BY name";
		$result = $this->execute($query);
		$views = [];
		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $view) {
			$view = array_diff_key($view, $keys);
			$views[$view['name']] = $view;
		}
		return $views;
	}
	/**
	 * Retrieves the primary key of a specified table.
	 *
	 * @param string $table The name of the table to get the primary key from.
	 * @return string|null The primary key of the table, or null if not found.
	 */
	public function getPrimaryKey($table) {
		if (is_string($table)) {
			$columns = $this->execute("PRAGMA table_info(`$table`)");
		} else {
			$columns = $table->columns;
		}
		$pk = array_filter($columns, fn($column) => $column['pk'] === 1);
		if (empty($pk)) {
			$pk = array_filter($columns, fn($column) => preg_match("~".self::$primary_key_pattern."~", $column['name']));
		}
		$pk = array_map(function ($column) {
			return $column['name'];
		}, $pk);
		
		return $pk;
	}
	/**
	 * Retrieves the columns of a specified table.
	 *
	 * @param string $table The name of the table to retrieve columns from.
	 * @return array An array of column names.
	 */
	public function getColumns($table) {
		if (!is_string($table)) {
			$table = $table->name;
		}
		$query = "PRAGMA table_info(`{$table}`)";
		$columns = $this->execute($query);
		$names = array_map(fn($column) => $column['name'], $columns);
		$columns = array_combine($names, $columns);
		return $columns;
	}
	/**
	 * Retrieves the indexes for a specified table in the SQLite database.
	 *
	 * @param string $table The name of the table to retrieve indexes for.
	 * @return array An array of indexes for the specified table.
	 */
	public function getIndexes($table) {
		$query = "PRAGMA index_list(`$table`)";
		$result = $this->execute($query);
		$indexes = [];
		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $index) {
			$index = array_diff_key($index, $keys);
			$indexes[$index['name']] = $index;
		}
		return $indexes;
	}
	/**
	 * Retrieves the foreign keys for a specified table.
	 *
	 * @param string $table The name of the table to retrieve foreign keys from.
	 * @return array An array of foreign key information.
	 */
	public function getForeignKeys($table) {
		$query = "PRAGMA foreign_key_list(`$table`)";
		$result = $this->execute($query);
		$foreignKeys = [];
		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $foreignKey) {
			$foreignKey = array_diff_key($foreignKey, $keys);
			$foreignKeys[$foreignKey['from']] = $foreignKey;
		}
		return $foreignKeys;
	}
}
