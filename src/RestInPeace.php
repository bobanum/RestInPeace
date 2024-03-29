<?php

namespace RestInPeace;

class RestInPeace {
	const SCHEMA_CACHE = 86400;
	const CONFIG_PATH = ".";
	public static $root = null;
	private static $_schema = null;
	protected static $app_root = null;
	protected static $db = null;
	public static $included_tables = [];
	public static $excluded_tables = [];
	public static $hidden_tables = [];

	static public function guard() {
	}
	static public function app_path($path = "") {
		if (self::$app_root === null) {
			self::$app_root = Config::get('APP_PATH', dirname($_SERVER['DOCUMENT_ROOT']));
			// Testing for relative path
			$temp = dirname($_SERVER['DOCUMENT_ROOT']) . "/" . self::$app_root;
			if (file_exists($temp)) {
				self::$app_root = $temp;
			}
			// Trying to find the .env file
			$temp = self::$app_root;
			while (file_exists($temp) && !file_exists("{$temp}/.env")) {
				$temp = dirname($temp);
			}
			if (file_exists("{$temp}/.env")) {
				self::$app_root = $temp;
			}
		}
		if (empty($path)) {
			return self::$app_root;
		}
		return self::$app_root . "/" . $path;
	}
	static public function database_path($path = '') {
		return self::absolutePath(Config::get('DATABASE_PATH', 'database'), $path);
	}
	static public function config_path($path = '') {
		return self::absolutePath(Config::get('CONFIG_PATH', 'config'), $path);
	}
	static public function absolutePath($path, $file = "") {
		$result = self::app_path($path);
		if (!file_exists($result)) {
			$result = $path;
		}
		$result = str_replace('\\', '/', realpath($result) ?: $result);
		if (!empty($file)) {
			$result .= "/" . $file;
		}
		return $result;
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
	static function getAll($table, $suffix = "index") {
		$schema = self::getSchema();
		if (!isset($schema['tables'][$table])) {
			return Response::replyCode(404);
		}
		self::connect();
		$table = Table::from($schema['tables'][$table], self::$db);
		$result = $table->all($suffix, ['id'=>1, 'limit'=>10, 'offset'=>0, 'by'=>'id', 'order'=>'ASC']);
		////
		// Adding HATEOAS
		$table->addHateoasArray($result);

		$result = [
			"count" => count($result),
			"url" => $table->getUrl(),
			"results" => $result,
		];
		return $result;
	}
	static function getOne($table, $id, $suffix = "index") {
		$schema = self::getSchema();
		
		if (!isset($schema['tables'][$table])) {
			return Response::replyCode(404);
		}
		self::connect();
		$table = Table::from($schema['tables'][$table], self::$db);
		$result = $table->find($id, $suffix);
		////
		// Adding HATEOAS
		$table->addHateoasArray($result);

		return $result;
	}
	static function getRelated($table, $id, $related) {
		// $schema = self::getSchema();
		$table = self::getSchemaTable($table);
		// vdd($table);
		if (!$table) {
			return Response::replyCode(404);
		}
		/** @var Table $table */
		// $table = $table['tables'][$table];
		$data = $table->related($related, $id);

		return $data;
	}
	public static function analyseDb() {
		return self::$db->analyse();
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
		return (!empty($clients) ||
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
		if (self::$_schema === null) {
			$filename = sprintf("schema.%s.php", basename(Config::get('DB_DATABASE', 'schema')));
			$schema = config::load($filename, true);
			if ($schema === false) {
				$schema = self::analyseDb();
				$schema['updated_at'] = time();
				Config::output($filename, $schema);
			} else {
				$schema['tables'] = array_map(fn ($table) => Table::from($table, self::$db), $schema['tables'] ?? []);
				$schema['views'] = array_map(fn ($view) => View::from($view, self::$db), $schema['views'] ?? []);
			}
			self::$_schema = $schema;
		}
		return self::$_schema;
	}
	static public function getSchemaView($view) {
		$schema = self::getSchema();
		if (!isset($schema['views'][$view])) {
			return false;
		}
		return View::from($schema['views'][$view]);
	}
	static public function getSchemaTable($table) {
		$schema = self::getSchema();
		if (!isset($schema['tables'][$table])) {
			return false;
		}
		return Table::from($schema['tables'][$table]);
	}
	static public function isVisible($table) {
		if (is_string($table)) {
			$table = self::getSchemaTable($table);
		}
		$table = Table::from($table);
		if (!empty(self::$hidden_tables) && in_array($table->name, self::$hidden_tables)) {
			return false;
		}
		if (!empty($table->is_junction_table)) {
			return false;
		}
		return true;
	}
	public static function init() {
		self::checkClients();

		if (file_exists(self::config_path('restinpeace.php'))) {
			$config = require_once self::config_path('restinpeace.php');
			foreach ($config as $key => $value) {
				if (property_exists(self::class, $key)) {
					self::$$key = $value;
				}
			}
		}

		$pdo = self::connect();

		if (!$pdo) {
			Response::replyCode(503);
		}
		$host = $_SERVER['HTTP_HOST'];
		$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
		self::$root = sprintf('%s://%s', $protocol, $host);
	}
}
RestInPeace::init();
