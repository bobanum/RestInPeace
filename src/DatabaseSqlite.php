<?php

namespace RestInPeace;

class DatabaseSqlite extends Database {
	public $database;
	public $username;
	public $password;
	public $host;
	public $port;
	static public $excluded_analysis_keys = ['rootpage', 'sql', 'tbl_name', 'type'];
	public function __construct($database = 'database/db.sqlite') {
		$this->database = str_replace('\\', '/', $database); // Fix for Windows (backslashes in path
	}
	function newPDO($options = []) {
		$options = self::$connectionOptions + [
			\PDO::ATTR_TIMEOUT => 3,
		] + $options;
		$database = $this->database;
		$dbPath = dirname($_SERVER['DOCUMENT_ROOT']);
		if ($database[0] === '/') {
			$dbPath .= $database;
		} else if (substr($database, 0, 2) === './') {
			$dbPath .= substr($database, 1);
		} else if (substr($database, 0, 3) === '../') {
			$dbPath .= "/" . $database;
		} else {
			$dbPath = $database;
		}

		$dbPath = realpath($dbPath);
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
	static function fromConfig() {
		return new static(
			Config::get('DB_DATABASE', 'database/db.sqlite')
		);
	}
	public function getTables($type='table') {
		$query = "SELECT * FROM sqlite_master WHERE type = '$type' ORDER BY name";
		$result = $this->execute($query);
		$tables = [];

		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $table) {
			$table = array_diff_key($table, $keys);
			$table['columns'] = $this->getColumns($table['name']);
			$table['indexes'] = $this->getIndexes($table['name']);
			$table['primary_key'] = $this->getPrimaryKey($table['name']);
			$tables[$table['name']] = $table;
		}
		foreach ($tables as &$table) {
			$table['foreign_keys'] = $this->getForeignKeys($table['name']);
		}
		return $tables;
	}
	public function getPrimaryKey($table) {
		$query = "PRAGMA table_info(`$table`)";
		$result = $this->execute($query);
		$pk = array_filter($result, function ($column) {
			return $column['pk'] === 1;
		});
		$pk = array_map(function ($column) {
			return $column['name'];
		}, $pk);
		
		return $pk;
	}
	public function getColumns($table) {
		$query = "PRAGMA table_info(`$table`)";
		$result = $this->execute($query);
		$columns = [];
		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $column) {
			$column = array_diff_key($column, $keys);
			$columns[$column['name']] = $column;
		}
		return $columns;
	}
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
	public function getViews() {
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
