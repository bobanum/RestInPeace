<?php
namespace RestInPeace;
use OAuth;
/**
 * Represents the RestInPeace class.
 */
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

	/**
	 * Returns the absolute path to the application directory.
	 *
	 * @param string $path Optional path to append to the application directory.
	 * @return string The absolute path to the application directory.
	 */
	static public function app_path($path = "") {

		if (self::$app_root === null) {
			self::$app_root = Config::get('APP_PATH', Config::env_path());
			// 503 error
			if (!self::$app_root) {
				exit(Response::replyCode(503));
			}
		}
		if (empty($path)) {
			return self::$app_root;
		}
		return self::$app_root . "/" . $path;
	}

	/**
	 * Returns the absolute path for the given database path.
	 *
	 * @param string $path The path to append to the database path.
	 * @return string The absolute path.
	 */
	static public function database_path($path = '') {
		return self::absolutePath(Config::get('DATABASE_PATH', 'database'), $path);
	}

	/**
	 * Returns the absolute path for the given config path.
	 *
	 * @param string $path The path to append to the config path.
	 * @return string The absolute path.
	 */
	static public function config_path($path = '') {
		return self::absolutePath(Config::get('CONFIG_PATH', 'config'), $path);
	}

	/**
	 * Returns the absolute path for the given path and file.
	 *
	 * @param string $path The base path.
	 * @param string $file The file to append to the path.
	 * @return string The absolute path.
	 */
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

	/**
	 * Retrieves the columns of the RestInPeace class.
	 *
	 * @param bool $implode Whether to implode the columns into a string or not. Default is true.
	 * @return array|string The columns of the RestInPeace class. If $implode is true, the columns will be returned as a string.
	 */
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

	/**
	 * Adds parameters to the query array.
	 *
	 * This function allows you to add parameters to the query array by passing a reference to the array and the source of the parameters.
	 *
	 * @param array $query The query array to which the parameters will be added.
	 * @param mixed $source The source of the parameters. This can be an array, an object, or null.
	 * @return void
	 */
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

	/**
	 * Retrieves all records from the specified table.
	 *
	 * @param string $table The name of the table to retrieve records from.
	 * @return Query An array containing all the records from the specified table.
	 */
	static function query($table) {
		return new Query($table);
		// $schema = self::getSchema();
		// if (!isset($schema['tables'][$table])) {
		// 	return Response::replyCode(404);
		// }
		// self::connect();
		// $table = Table::from($schema['tables'][$table], self::$db);
		// $result = $table->all($suffix, ['id' => 1, 'limit' => 10, 'offset' => 0, 'by' => 'id', 'order' => 'ASC']);
		////
		// Adding HATEOAS
		// $table->addHateoasArray($result);

		// $result = [
		// 	"count" => count($result),
		// 	"url" => $table->getUrl(),
		// 	"results" => $result,
		// ];
		// return $result;
	}
	static function url(...$args) {
		$result = [
			($_SERVER['REQUEST_SCHEME'] ?? 'http') . ':/',
			$_SERVER['HTTP_HOST'],
		];
		if (!empty($_SERVER['PATH_INFO'])) {
			$uri = substr($_SERVER['REQUEST_URI'], 1);
			$uri = substr($uri, 0, -strlen($_SERVER['PATH_INFO']));
			$result[] = $uri;
		}
		array_push($result, ...$args);
		$result = implode("/", $result);
		return $result;
	}

	/**
	 * Retrieves a single record from the specified table based on the given ID.
	 *
	 * @param string $table The name of the table.
	 * @param int $id The ID of the record to retrieve.
	 * @param string $suffix The suffix to append to the find operation (default: "index").
	 * @return mixed The retrieved record, or a 404 response if the table does not exist.
	 */
	static function getOne($table, $id, $suffix = "index") {
		$modelClass = Model::findModel($table);
		$result = $modelClass::find($id)->fetchWith();
		return $result;

		// $schema = self::getSchema();

		// if (!isset($schema['tables'][$table])) {
		// 	return Response::replyCode(404);
		// }
		// self::connect();
		// $table = Table::from($schema['tables'][$table], self::$db);
		// $result = $table->find($id, $suffix)[0];

		// Adding HATEOAS
		// $table->addHateoasArray($result);
	}

	static function update($table, $id = null, $data = null) {
		$data = $data ?? $_POST;
		$id = $id ?? $data['id'];
		$className = __NAMESPACE__ . "\\Models\\" . ucfirst($table);
		if (!class_exists($className)) {
			return Response::replyCode(404);
		}
		if (empty($id)) {
			$id = null;
		}
		if (!empty($data['id']) && $id !== $data['id']) {
			throw new \Exception("Error Processing Request", 1);
		}
		$model = $className::get($id);
		$model->fill($data);
		$model->save();
		return [
			'status' => 'success',
			'data' => $model->attributes
		];
	}
	/**
	 * Analyzes the database and returns the analysis result.
	 * @param Database $db The database to analyze.
	 *
	 * @return mixed The analysis result.
	 */
	public static function analyseDb($db = null) {
		$db = $db ?? self::connect();
		vd();
		Config::outputModels($db->analyse());
		return ['status' => 'success'];
	}

	/**
	 * Checks the clients.
	 *
	 * This method is responsible for checking the clients.
	 * It performs some specific actions related to the clients.
	 * 
	 * @return void
	 */
	protected static function checkClients() {
		$clients = Config::get('CLIENTS', '');

		if (!self::isAllowed($clients)) {
			return Response::replyCode(403);
		}
		return true;
	}

	/**
	 * Checks if the given clients are allowed.
	 *
	 * @param array $clients The clients to check.
	 * @return bool Returns true if the clients are allowed, false otherwise.
	 */
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

	/**
	 * Retrieves the path information for the current request.
	 *
	 * @return string The path information.
	 */
	protected static function getPathInfo() {
		if (isset($_SERVER['PATH_INFO'])) {
			return $_SERVER['PATH_INFO'];
		}
		if (isset($_SERVER['ORIG_PATH_INFO'])) {
			return $_SERVER['ORIG_PATH_INFO'];
		}
		return preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
	}

	/**
	 * Connects to the database.
	 *
	 * @return Database The database connection.
	 */
	public static function connect() {
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

	/**
	 * Retrieves the schema.
	 *
	 * @return mixed The schema.
	 */
	static public function getSchema() {
		if (self::$_schema === null) {
			$filename = sprintf("schema.%s.php", basename(Config::get('DB_DATABASE', 'schema')));
			$schema = config::load($filename, true);
			if ($schema === false) {
				// vdd($schema);
				$schema = self::analyseDb();
				$schema['updated_at'] = time();
				// Config::output($filename, $schema);
				vd();
				Config::outputModels($schema);
			} else {
				$schema['tables'] = array_map(fn($table) => Table::from($table, self::$db), $schema['tables'] ?? []);
				$schema['views'] = array_map(fn($view) => View::from($view, self::$db), $schema['views'] ?? []);
			}
			self::$_schema = $schema;
		}
		return self::$_schema;
	}

	/**
	 * Retrieves the schema view for a given view.
	 *
	 * @param string $view The name of the view.
	 * @return mixed The schema view for the given view.
	 */
	static public function getSchemaView($view) {
		$schema = self::getSchema();
		if (!isset($schema['views'][$view])) {
			return false;
		}
		return View::from($schema['views'][$view]);
	}

	/**
	 * Retrieves the schema table for a given table.
	 *
	 * @param string $table The name of the table.
	 * @return mixed The schema table for the given table.
	 */
	static public function getSchemaTable($table) {
		$table .= '';
		$schema = self::getSchema();
		if (!isset($schema['tables']) || !isset($schema['tables'][$table])) {
			return false;
		}
		return Table::from($schema['tables'][$table]);
	}

	/**
	 * Determines if a table is visible.
	 *
	 * @param string $table The name of the table.
	 * @return bool Returns true if the table is visible, false otherwise.
	 */
	static public function isVisible($table) {
		return true;
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

	/**
	 * Initializes the RestInPeace class.
	 *
	 * This method is responsible for initializing the RestInPeace class and performing any necessary setup tasks.
	 * It should be called before using any other methods or properties of the RestInPeace class.
	 *
	 * @return void
	 */
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
