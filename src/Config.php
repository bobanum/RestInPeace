<?php

namespace RestInPeace;

use \Dotenv\Dotenv;
use RestInPeace\RestInPeace as RIP;

/**
 * Class Config
 *
 * This class is part of the RestInPeace library and is used for configuration purposes.
 *
 * @package bobanum\restinpeace
 */
class Config {
	/** @var array $attributes Static array to hold attribute configurations */
	static $attributes = [];
	/**
	 * Retrieves a configuration value based on the provided key.
	 *
	 * @param string $key The key for the configuration value.
	 * @param mixed $default The default value to return if the key does not exist. Default is null.
	 * @return mixed The configuration value associated with the key, or the default value if the key is not found.
	 */
	static function get($key, $default = null) {
		$result = self::$attributes[$key] ?? $_ENV["RIP_{$key}"] ?? $_ENV["RESTINPEACE_{$key}"] ?? $_ENV[$key] ?? $default;
		if ($result === 'false') return false;
		if ($result === 'true') return true;
		if (is_bool($result)) return boolval($result);
		if (is_numeric($result)) return floatval($result);
		return $result;
	}
	/**
	 * Magic method to get the value of a property.
	 *
	 * This method is called when accessing a property that is not defined in the class.
	 *
	 * @param string $key The name of the property to get.
	 * @return mixed The value of the property, or null if the property does not exist.
	 */
	function __get($key) {
		return self::get($key);
	}
	/**
	 * Magic method to set the value of a property.
	 *
	 * @param string $key The name of the property to set.
	 * @param mixed $value The value to set for the property.
	 */
	function __set($key, $value) {
		self::$attributes[$key] = $value;
	}
	/**
	 * Initializes the configuration settings.
	 *
	 * This static method is responsible for setting up the initial configuration
	 * for the application. It should be called once during the application startup
	 * to ensure all necessary settings are properly configured.
	 *
	 * @return void
	 */
	static function init() {
		$dotenv = Dotenv::createImmutable(RIP::app_path());
		$dotenv->load();
		return;
	}
	/**
	 * Returns the full path by appending the given path to a base path.
	 *
	 * @param string $path The path to append. Default is an empty string.
	 * @return string The full path.
	 */
	static public function path($path = '') {
		return RIP::absolutePath(self::get('CONFIG_PATH', 'config'), $path);
	}
	/**
	 * Checks if the given file has timed out.
	 *
	 * @param string $filename The path to the file to check.
	 * @return bool Returns true if the file has timed out, false otherwise.
	 */
	static public function isTimedOut($filename) {
		$timeout = self::get('SCHEMA_CACHE', RIP::SCHEMA_CACHE);
		$filepath = self::path($filename);
		if (!file_exists($filepath)) return true;
		if (time() - filemtime($filepath) < $timeout) return false;
		return true;
	}
	/**
	 * Loads the configuration from the specified file.
	 *
	 * @param string $filename The path to the configuration file.
	 * @param bool $checkTimeout Optional. Whether to check for a timeout. Default is true.
	 * @return mixed The loaded configuration data.
	 */
	static public function load($filename, $checkTimeout = true) {
		$filepath = self::path($filename);
		if (!file_exists($filepath)) return false;
		if ($checkTimeout && self::isTimedOut($filename)) return false;
		return include $filepath;
	}
	/**
	 * Normalizes the given data.
	 *
	 * This function takes an input data and processes it to ensure it conforms
	 * to a standard format or structure. The exact normalization process depends
	 * on the implementation details within the function.
	 *
	 * @param mixed $data The data to be normalized. This can be of any type.
	 * @return mixed The normalized data.
	 */
	static public function normalizeData($data) {
		if (is_object($data)) {
			if (method_exists($data, 'toConfig')) {
				$data = $data->toConfig();
			} else {
				$data = (array) $data;
			}
			// Remove private properties
			$data = array_filter($data, fn ($key) => $key[0] !== "\0", ARRAY_FILTER_USE_KEY);
		}
		if (!is_array($data)) {
			return $data;
		}
		$data = array_map(fn ($item) => self::normalizeData($item), $data);
		
		return $data;
	}
	/**
	 * Outputs data to a specified file.
	 *
	 * @param string $filename The name of the file to output the data to.
	 * @param mixed $data The data to be output to the file.
	 */
	public static function output($filename, $data) {
		// $filename = sprintf("schema.%s.php", basename(Config::get('DB_DATABASE', 'schema')));
		$filepath = self::path($filename);
		$data = self::normalizeData($data);
		$output = "\n" . var_export($data, true) . ";";
		$output = preg_replace('~((?:\r\n|\n\r|\r|\n)\s*)array \(~', '[', $output);
		$output = preg_replace('~((?:\r\n|\n\r|\r|\n)\s*)\)([\,\;])~', '$1]$2', $output);
		$output = str_replace('  ', "\t", $output);
		$output = trim($output);
		$output = "<?php\nreturn " . $output;
		self::mkdir(dirname($filepath));
		file_put_contents($filepath, $output);
		return $data;
	}
	/**
	 * Creates a directory with the specified path and mode.
	 *
	 * @param string $path The path where the directory should be created.
	 * @param int $mode The permissions mode for the directory. Default is 0777.
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public static function mkdir($path, $mode = 0777) {
		if (!file_exists($path)) {
			mkdir($path, $mode, true);
		}
		return $path;
	}
	/**
	 * Retrieves the parameters from the configuration.
	 *
	 * @return array The configuration parameters.
	 */
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
}
Config::init();
