<?php

namespace RestInPeace;

class RestInPeace {
	const SCHEMA_CACHE = 86400;
	const CONFIG_PATH = ".";
	protected static $root = null;
	protected static $db = null;

	static public function guard() {
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
	static function addParams(&$query = [], $source = null) {
		$source = $source ?? $_GET;
		if (isset($source['by'])) {
			if (!isset($source['order'])) {
				$source['order'] = 'ASC';
			}

			$query[] = sprintf('ORDER BY "%s" %s', $source['by'], $source['order']);
		}

		if (isset($source['limit'])) {
			$query[] = sprintf('LIMIT %u', $source['limit']);

			if (isset($source['offset'])) {
				$query[] = sprintf('OFFSET %u', $source['offset']);
			}
		}
		return $query;
	}
	static function actionGetAll($table, $suffix = "index") {
		$cols = self::getCols();

		$query = [
			sprintf('SELECT %s FROM `%s`', $cols, $table."_index"),
		];
		self::connect();
		self::addParams($query);
		$result = self::$db->execute($query);

		if ($result === false) {
			return Response::replyCode(404);
		}

		if (empty($result)) {
			return Response::replyCode(204);
		}
		array_walk($result, function (&$row) use ($table) {
			$row['url'] = sprintf("%s/%s/%s", self::$root, $table, $row['id']);
		});
		$result = [
			"count" => count($result),
			"results" => $result,
		];
		return Response::reply($result);
	}
	public static function analyseDb() {
		
		return [
			"tables" => self::$db->getTables(),
			"views" => self::$db->getTables('view'),
		];
	}
	public static function init() {
		self::checkClients();
		if (strcmp(PHP_SAPI, 'cli') === 0) {
			exit('ArrestDB should not be run from CLI.' . PHP_EOL);
		}

		$pdo = self::connect();

		if (!$pdo) {
			Response::replyCode(503);
		}
		$host = $_SERVER['HTTP_HOST'];
		$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
		self::$root = sprintf('%s://%s', $protocol, $host);
		// exit(self::$root);
	}
	protected static function checkClients() {
		$clients = Config::get('CLIENTS', '');

		if (!self::isAllowed($clients)) {
			return Response::replyCode(403);
		}
		return true;
	}
	protected static function isAllowed($clients) {
		if (is_string($clients)) {
			$clients = preg_split('~\s*,\s*~', $clients, -1, PREG_SPLIT_NO_EMPTY);
		}
		return (
			!empty($clients) ||
			!in_array($_SERVER['REMOTE_ADDR'], (array) $clients) ||
			empty($_SERVER['HTTP_REFERER']) ||	// TODO CHECK IF LOCAL
			!in_array($_SERVER['HTTP_REFERER'], (array) $clients)
		);
	}
	protected static function getPathInfo() {
		if (isset($_SERVER['PATH_INFO'])) {
			return $_SERVER['PATH_INFO'];
		}
		if (isset($_SERVER['ORIG_PATH_INFO'])) {
			return $_SERVER['ORIG_PATH_INFO'];
		}
		return preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
	}
	protected static function connect() {
		if (!empty(self::$db)) {
			return self::$db;
		}
		try {
			$connection = Config::get('DB_CONNECTION', 'sqlite');
			if (strcasecmp($connection, 'sqlite') === 0) {
				self::$db = DatabaseSqlite::fromConfig();
			} else if (strcasecmp($connection, 'mysql') === 0) {
				self::$db = DatabaseMysql::fromConfig();
			} else {
				throw new \Exception("Unknown Database Driver");
			}
		} catch (\Exception $exception) {
			exit(Response::replyCode(503));
		}
		return self::$db;
	}
	static public function getSchema() {
		vd($_SERVER);
		$filepath = dirname() + Config::get('SCHEMA_CACHE', self::SCHEMA_CACHE) . 'schema.json';
		if (file_exists(Config::get('SCHEMA_CACHE', self::SCHEMA_CACHE) . 'schema.json') && time() - filemtime('schema.json') < Config::get('SCHEMA_CACHE', self::SCHEMA_CACHE)) {
			return json_decode(file_get_contents('schema.json'), true);
		} else {
			$schema = self::analyseDb();
			$schema['updated_at'] = time();
			file_put_contents('schema.json', json_encode($schema));
			return $schema;
		}
	}
}
RestInPeace::init();
