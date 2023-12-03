<?php
namespace RestInPeace;

class RestInPeace {
	protected static $root = null;
	protected static $db = null;

	static public function guard() {
	}
	public static function init() {
		self::checkClients();
		if (strcmp(PHP_SAPI, 'cli') === 0) {
			exit('ArrestDB should not be run from CLI.' . PHP_EOL);
		}

		// $pdo = self::connect();

		// if (!$pdo) {
		// 	exit(self::reply(self::$HTTP[503]));
		// }

		self::$root = self::getPathInfo();
	}
	protected static function checkClients() {
		$clients = Config::get('CLIENTS', '');
		
		if (!self::isAllowed($clients)) {
			return Response::sendCode(403);
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
				return self::$db = DatabaseSqlite::fromConfig();
			} else if (strcasecmp($connection, 'mysql') === 0) {
				return self::$db = DatabaseMysql::fromConfig();
			} else {
				throw new \Exception("Unknown Database Driver");
			}
		} catch (\Exception $exception) {
			exit(Response::sendCode(503));
		}
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
}
RestInPeace::init();