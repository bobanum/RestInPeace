<?php
namespace RestInPeace;
use \Dotenv\Dotenv;

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