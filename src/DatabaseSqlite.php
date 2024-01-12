<?php

namespace RestInPeace;

class DatabaseSqlite extends Database {
	public $database;
	public $username;
	public $password;
	public $host;
	public $port;
	static public $excluded_analysis_keys = ['rootpage', 'sql', 'tbl_name', 'type'];
	public function __construct($database = 'db.sqlite') {
		$this->database = RestInPeace::database_path($database);
	}
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
	static function fromConfig() {
		return new static(Config::get('DB_DATABASE', 'db.sqlite'));
	}
	public function getTables() {
		$query = "SELECT * FROM sqlite_master WHERE type = 'table' ORDER BY name";
		$result = $this->execute($query);
		$tables = [];

		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $table) {
			$name = $table['name'];
			if (!RestInPeace::isValidTable($name)) {
				continue;
			}
			$table = array_diff_key($table, $keys);
			$table['columns'] = $this->getColumns($name);
			$table['is_junction_table'] = self::isJunctionTable($table['columns']);
			$table['indexes'] = $this->getIndexes($name);
			$table['primary_key'] = $this->getPrimaryKey($name);
			$tables[$name] = $table;
		}
		foreach ($tables as &$table) {
			$table['foreign_keys'] = $this->getForeignKeys($table['name']);
		}
		return $tables;
	}
	public function getViews() {
		$query = "SELECT * FROM sqlite_master WHERE type = 'view' ORDER BY name";
		$result = $this->execute($query);
		$views = [];

		$keys = array_flip(self::$excluded_analysis_keys);
		foreach ($result as $view) {
			$name = $view['name'];
			$view = array_diff_key($view, $keys);
			$view['columns'] = $this->getColumns($name);
			$views[$name] = $view;
		}
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
	static function isJunctionTable($columns) {
		$columns = array_filter($columns, function ($column) {
			$exclude = ['id', 'created_at', 'updated_at'];
			if (in_array($column['name'], $exclude)) return false;
			if ($column['pk'] === 1) return false;
			if (substr($column['name'], -3) === '_id') return false;
			return true;
		});
		return count($columns) === 0;
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
