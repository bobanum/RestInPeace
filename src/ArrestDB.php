<?php
namespace ArrestDB;

function XXXconfig($key, $default = null) {
	return $_ENV[$key] ?? $default;
}

class ArrestDB {
	public static $primary_key = 'id';

	protected static $db = null;
	protected static $root = null;
	protected static $result = [];
	public static $XXXHTTP = [
		200 => [
			'success' => [
				'code' => 200,
				'status' => 'OK',
			],
		],
		201 => [
			'success' => [
				'code' => 201,
				'status' => 'Created',
			],
		],
		204 => [
			'error' => [
				'code' => 204,
				'status' => 'No Content',
			],
		],
		400 => [
			'error' => [
				'code' => 400,
				'status' => 'Bad Request',
			],
		],
		403 => [
			'error' => [
				'code' => 403,
				'status' => 'Forbidden',
			],
		],
		404 => [
			'error' => [
				'code' => 404,
				'status' => 'Not Found',
			],
		],
		409 => [
			'error' => [
				'code' => 409,
				'status' => 'Conflict',
			],
		],
		503 => [
			'error' => [
				'code' => 503,
				'status' => 'Service Unavailable',
			],
		],
	];
	public static $XXXconnectionOptions = [
		\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
		\PDO::ATTR_STRINGIFY_FETCHES => false,
	];

	public static function test() {
		echo "Hello World";
	}
	public static function init() {
		$root = dirname($_SERVER['DOCUMENT_ROOT']);
		$dotenv = \Dotenv\Dotenv::createImmutable($root);
		$dotenv->load();

		if (strcmp(PHP_SAPI, 'cli') === 0) {
			exit('ArrestDB should not be run from CLI.' . PHP_EOL);
		}

		self::checkClients();

		$db = self::connect();

		if (!$db) {
			exit(self::reply(self::$HTTP[503]));
		}

		self::$root = self::getPathInfo();
	}

	protected static function checkClients() {
		$clients = config('ARRESTDB_CLIENTS', '');
		$clients = preg_split('/\s*,\s*/', $clients, -1, PREG_SPLIT_NO_EMPTY);

		if (!self::isAllowed($clients)) {
			exit(self::reply(self::$HTTP[403]));
		}
		return true;
	}

	protected static function XXXrequestMethod() {
		if (array_key_exists('_method', $_GET)) {
			return strtoupper(trim($_GET['_method']));
		}

		if (array_key_exists('HTTP_X_HTTP_METHOD_OVERRIDE', $_SERVER)) {
			return strtoupper(trim($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']));
		}

		if (!isset($_SERVER['REQUEST_METHOD'])) {
			return 'CLI';
		}

		return $_SERVER['REQUEST_METHOD'];
	}

	protected static function XXXconnect() {
		if (self::$db !== null) {
			return self::$db;
		}
		try {
			$connection = config('ARRESTDB_CONNECTION', 'sqlite');
			if (strcasecmp($connection, 'sqlite') === 0) {
				return self::$db = self::connectSqlite();
			} else if (strcasecmp($connection, 'mysql') === 0) {
				return self::$db = self::connectMysql();
			} else {
				throw new \Exception("Unknown Database Driver");
			}
		} catch (\Exception $exception) {
			exit(self::reply(self::$HTTP[503]));
		}
	}
	protected static function XXXconnectMysql($options = []) {
		$options = self::$connectionOptions + $options;
		$options += [
			\PDO::ATTR_AUTOCOMMIT => true,
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci", time_zone = "+00:00";',
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		];
		$host = config('DB_HOST', 'localhost');
		$port = config('DB_PORT', '3306');
		$dbname = config('DB_DATABASE', 'database');
		$username = config('DB_USERNAME', 'root');
		$password = config('DB_PASSWORD', '');
		$dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $host, $port, $dbname, $username, $password);
		return new \PDO($dsn, $username, $password, $options);
	}
	protected static function XXXconnectSqlite($options = []) {
		$options = self::$connectionOptions + $options;
		$options += [
			\PDO::ATTR_TIMEOUT => 3,
		];
		$dbPath = config('DB_DATABASE', 'database/db.sqlite');
		$dbPath = realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/' . $dbPath);
		if (empty($dbPath)) {
			throw new \Exception("Database not found");
		}
		$dsn = sprintf('sqlite:%s', $dbPath);
		$db = new \PDO($dsn, null, null, $options);
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
			$db->exec(sprintf('PRAGMA %s=%s;', $key, $value));
		}
		return $db;
	}
	public static function querySql($query) {
		if (is_array($query)) {
			$query = sprintf('%s;', implode(' ', $query));
		}
		try {
			if (strncasecmp(self::$db->getAttribute(\PDO::ATTR_DRIVER_NAME), 'mysql', 5) === 0) {
				$query = strtr($query, '"', '`');
			}

			if (empty(self::$result[$hash = crc32($query)])) {
				self::$result[$hash] = self::$db->prepare($query);
			}
			$data = array_slice(func_get_args(), 1);

			if (count($data, COUNT_RECURSIVE) > count($data)) {
				$data = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data)), false);
			}

			if (self::$result[$hash]->execute($data) === true) {
				$sequence = null;

				if ((strncmp(self::$db->getAttribute(\PDO::ATTR_DRIVER_NAME), 'pgsql', 5) === 0) && (sscanf($query, 'INSERT INTO %s', $sequence) > 0)) {
					$sequence = sprintf('%s_id_seq', trim($sequence, '"'));
				}

				switch (strstr($query, ' ', true)) {
					case 'INSERT':
					case 'REPLACE':
						return self::$db->lastInsertId($sequence);

					case 'UPDATE':
					case 'DELETE':
						return self::$result[$hash]->rowCount();

					case 'SELECT':
					case 'EXPLAIN':
					case 'PRAGMA':
					case 'SHOW':
						return self::fetch(self::$result[$hash]);
				}
				return true;
			}
		} catch (\Exception $exception) {
			return false;
		}
	}

	public static function XXXreply($data) {
		$bitmask = 0;
		$options = ['UNESCAPED_SLASHES', 'UNESCAPED_UNICODE'];

		if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			$options[] = 'PRETTY_PRINT';
		}

		foreach ($options as $option) {
			$bitmask |= defined('JSON_' . $option) ? constant('JSON_' . $option) : 0;
		}
		$result = json_encode($data, $bitmask);
		if ($result !== false) {
			$callback = null;

			if (array_key_exists('callback', $_GET)) {
				$callback = trim(preg_replace('~[^[:alnum:]\[\]_.]~', '', $_GET['callback']));

				if (!empty($callback)) {
					$result = sprintf('%s(%s);', $callback, $result);
				}
			}

			if (!headers_sent()) {
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Expose-Headers: x-http-method-override');
				header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
				header(sprintf('Content-Type: application/%s; charset=utf-8', empty($callback) ? 'json' : 'javascript'));
			}
		}

		return $result;
	}

	public static function XXXserve($on = null, $route = null, $callback = null) {
		if (!empty($on) && (strcasecmp(self::requestMethod(), $on) !== 0)) return false;

		$route = preg_replace('~/\(#[a-z]+\)\?~i', '(?:$0)?', $route);
		$route = str_replace(['#any', '#num'], ['[^/]++', '[0-9]++'], $route);
		//Note: "/table/column/" will not mean "WHERE column=''" since trailing / is always ignored
		if (preg_match('~^' . $route . '/?$~i', self::$root, $parts) > 0) {
			if (empty($callback)) return true;
			if (is_string($callback)) {
				return exit(call_user_func_array([__CLASS__, $callback], array_slice($parts, 1)));
			} else {
				return exit(call_user_func_array($callback, array_slice($parts, 1)));
			}
		}
		return true;
	}
	public static function serveRest() {
		self::serve('GET', '/(#any)/(#any)/(#any)', 'ActionGetAny');
		self::serve('GET', '/(#any)', 'ActionGetAll');
		self::serve('GET', '/(#any)/(#num)', 'ActionFind');
		self::serve('DELETE', '/(#any)/(#num)', 'ActionDelete');
		self::serve('POST', '/(#any)', 'ActionPost');
		self::serve('PUT', '/(#any)/(#num)', 'ActionPut');
	}
	protected static function XXXgetPathInfo() {
		if (isset($_SERVER['PATH_INFO'])) {
			return $_SERVER['PATH_INFO'];
		}
		if (isset($_SERVER['ORIG_PATH_INFO'])) {
			return $_SERVER['ORIG_PATH_INFO'];
		}
		return preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
	}

	public static function getCols($implode = true) {
		if (!isset($_GET['cols'])) {
			return "*";
		}
		$cols = explode(",", $_GET['cols']);
		$cols = array_map(function ($col) {
			$col = htmlspecialchars($col);
			return sprintf('`%s`', $col);
		}, $cols);
		if ($implode === true) {
			$cols = implode(",", $cols);
		}
		return $cols;
	}

	public static function getTableCols($table, $implode = true) {
		$result = self::querySql(sprintf('PRAGMA table_info("%s");', $table));
		// if ($implode === true) {
		// 	$cols = implode(",", $cols);
		// }
		return $result;
	}

	public static function getParams() {
		$query = [];
		if (isset($_GET['by'])) {
			if (!isset($_GET['order'])) {
				$_GET['order'] = 'ASC';
			}

			$query[] = sprintf('ORDER BY "%s" %s', $_GET['by'], $_GET['order']);
		}

		if (isset($_GET['limit'])) {
			$query[] = sprintf('LIMIT %u', $_GET['limit']);

			if (isset($_GET['offset'])) {
				$query[] = sprintf('OFFSET %u', $_GET['offset']);
			}
		}
		return $query;
	}

	public static function getData() {
		$http = strtoupper($_SERVER['REQUEST_METHOD']);
		if (!in_array($http, ['POST', 'PUT'])) return [];

		if (isset($_POST) && is_array($_POST) && count($_POST) > 0) return $_POST;
		if (isset($_PUT) && is_array($_PUT) && count($_PUT) > 0) return $_PUT;	// If one day $_PUT is implemented

		$data = file_get_contents('php://input');

		if ($data === '') return [];

		if (preg_match('~^\x78[\x01\x5E\x9C\xDA]~', $data) > 0) {
			$data = gzuncompress($data);
		}

		if (!array_key_exists('CONTENT_TYPE', $_SERVER)) return $data;

		if (strncasecmp($_SERVER["CONTENT_TYPE"], 'multipart/form-data', 19) === 0) {
			return HttpMultipartParser::parse_input()['variables'];
		}

		if (empty($data)) return [];

		if (strncasecmp($_SERVER['CONTENT_TYPE'], 'application/json', 16) === 0) {
			return json_decode($data, true);
		}

		if ((strncasecmp($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded', 33) === 0) && (strncasecmp($_SERVER['REQUEST_METHOD'], 'PUT', 3) === 0)) {
			//TODO CHECK (What if it is POST?)
			return array_merge($GLOBALS['_' . $http], $data);
		}

		return $data;
	}

	public static function fetch($stmt) {
		$result = $stmt->fetchAll();
		if (count($result) === 0) {
			return $result;
		}
		foreach (array_keys($result[0]) as $idx => $name) {
			$meta = $stmt->getColumnMeta($idx);
			if (isset($meta['sqlite:decl_type'])) {
				if (preg_match("#int|dec|num|real|float|long|short|double|byte|timestamp#i", $meta['sqlite:decl_type'])) {
					for ($i = 0, $len = count($result); $i < $len; $i += 1) {
						$result[$i][$name] = floatval($result[$i][$name]);
					}
				}
			}
		}
		return $result;
	}
	protected static function XXXisAllowed($clients) {
		return (
			(!empty($clients)) ||
			(!in_array($_SERVER['REMOTE_ADDR'], (array) $clients)) ||
			(empty($_SERVER['HTTP_REFERER'])) ||	// TODO CHECK IF LOCAL
			(!in_array($_SERVER['HTTP_REFERER'], (array) $clients))
		);
	}

	protected static function actionGetAny($table, $id, $data) {
		$cols = self::getCols();
		$query = [
			sprintf('SELECT %s FROM "%s"', $cols, $table),
			sprintf('WHERE "%s" %s ?', $id, ctype_digit($data) ? '=' : 'LIKE'),
		];

		$query = array_merge($query, self::getParams());

		$result = self::querySql($query, $data);

		if ($result === false) {
			return self::reply(self::$HTTP[404]);
		}

		if (empty($result)) {
			return self::reply(self::$HTTP[204]);
		}

		return self::reply($result);
	}
	protected static function actionGetAll($table) {
		$cols = self::getCols();
		$query = [
			sprintf('SELECT %s FROM "%s"', $cols, $table),
		];

		$query = array_merge($query, self::getParams());

		$result = self::querySql($query);

		if ($result === false) {
			return self::reply(self::$HTTP[404]);
		}

		if (empty($result)) {
			return self::reply(self::$HTTP[204]);
		}

		return self::reply($result);
	}
	protected static function actionFind($table, $id) {
		$cols = self::getCols();
		$query = sprintf('SELECT %s FROM "%s" WHERE "%s" = ? LIMIT 1;', $cols, $table, self::$primary_key);

		$result = self::querySql($query, $id);

		if ($result === false) {
			return self::reply(self::$HTTP[404]);
		}

		if (empty($result)) {
			return self::reply(self::$HTTP[204]);
		}

		return self::reply($result[0]);
	}
	protected static function actionDelete($table, $id) {
		$query = sprintf('DELETE FROM "%s" WHERE "%s" = ?;', $table, self::$primary_key);

		$result = self::querySql($query, $id);

		if ($result === false) {
			return self::reply(self::$HTTP[404]);
		}

		if (empty($result)) {
			return self::reply(self::$HTTP[204]);
		}

		return self::reply(self::$HTTP[200]);
	}
	protected static function actionPost($table) {
		if (empty($_POST) || !is_array($_POST)) {
			return self::reply(self::$HTTP[204]);
		}

		$queries = [];

		$formData = self::getData();
		//TODO Test thoroughly

		if (count($formData) === count($formData, COUNT_RECURSIVE)) {
			$formData = [$formData];
		}

		foreach ($formData as $row) {
			$data = [];

			foreach ($row as $key => $value) {
				$data[sprintf('"%s"', $key)] = $value;
			}

			$columns = implode(', ', array_keys($data));
			$values = implode(', ', array_fill(0, count($data), '?'));
			$query = sprintf('INSERT INTO "%s" (%s) VALUES (%s);', $table, $columns, $values);

			$queries[] = [
				$query,
				$data,
			];
		}

		if (count($queries) === 0) {
			return self::reply(self::$HTTP[409]);
		}
		if (count($queries) === 1) {
			$query = $queries[0];
			$result = self::querySql($query[0], $query[1]);
		} else {
			self::$db->beginTransaction();

			foreach ($queries as $query) {
				$result = self::querySql($query[0], $query[1]);
				if ($result === false) {
					self::$db->rollBack();
					break;
				}
			}
			if ($result !== false && self::$db->inTransaction()) {
				$result = self::$db->commit();
			}
		}

		if ($result === false) {
			return self::reply(self::$HTTP[409]);
		}

		return self::reply(self::$HTTP[201]);
	}
	protected static function actionPut($table, $id) {
		$formData = self::getData();
		//TODO Test thoroughly
		$data = [];

		foreach (array_keys($formData) as $key) {
			$data[] = sprintf('"%s" = ?', $key);
		}
		$data = implode(', ', $data);

		$query = sprintf('UPDATE "%s" SET %s WHERE "%s" = ?;', $table, $data, self::$primary_key);

		$result = ArrestDB::QuerySQL($query, $formData, $id);

		if ($result === false) {
			return ArrestDB::Reply(ArrestDB::$HTTP[409]);
		}
		return ArrestDB::Reply(ArrestDB::$HTTP[200]);
	}
}
ArrestDB::init();
