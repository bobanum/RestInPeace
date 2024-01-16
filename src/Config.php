<?php

namespace RestInPeace;

use \Dotenv\Dotenv;
use RestInPeace\RestInPeace as RIP;

class Config {
	static $attributes = [];
	static function get($key, $default = null) {
		$result = self::$attributes[$key] ?? $_ENV["RIP_{$key}"] ?? $_ENV["RESTINPEACE_{$key}"] ?? $_ENV[$key] ?? $default;
		if ($result === 'false') return false;
		if ($result === 'true') return true;
		if (is_bool($result)) return boolval($result);
		if (is_numeric($result)) return floatval($result);
		return $result;
	}
	function __get($key) {
		return self::get($key);
	}
	function __set($key, $value) {
		self::$attributes[$key] = $value;
	}
	static function init() {
		$dotenv = Dotenv::createImmutable(dirname(__DIR__));
		$dotenv->load();
		return;
	}
	static public function path($path = '') {
		return RIP::absolutePath(self::get('CONFIG_PATH', 'config'), $path);
	}
	static public function isTimedOut($filename) {
		$timeout = self::get('SCHEMA_CACHE', RIP::SCHEMA_CACHE);
		$filepath = self::path($filename);
		if (!file_exists($filepath)) return true;
		if (time() - filemtime($filepath) < $timeout) return false;
		return true;
	}
	static public function load($filename, $checkTimeout = true) {
		$filepath = self::path($filename);
		if (!file_exists($filepath)) return false;
		if ($checkTimeout && self::isTimedOut($filename)) return false;
		return include $filepath;
	}
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
		file_put_contents($filepath, $output);
		return $data;
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
}
Config::init();
