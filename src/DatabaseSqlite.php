<?php

namespace RestInPeace;

abstract class DatabaseSqlite extends Database {
	public $database;
	public $username;
	public $password;
	public $host;
	public $port;
	public $_pdo;

	public function __construct($database = 'database/db.sqlite') {
		$this->database = str_replace('\\', '/', $database); // Fix for Windows (backslashes in path
	}
	function connect($options = []) {
		if (!empty($this->_pdo)) {
			return $this->_pdo;
		}
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
		$this->_pdo = $pdo;
		return $pdo;
	}
	static function fromConfig() {
		return new static(
			Config::get('DB_DATABASE', 'database/db.sqlite')
		);
	}
}
