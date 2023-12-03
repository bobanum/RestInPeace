<?php

namespace RestInPeace;

class Request {
	static $method;
	static $uri;
	static $parts = [];
	static $placeholders = [
		'#any' => '[^/\?#]+',
		'#num' => '[0-9]+',
		'#alpha' => '[a-z]+',
		'#ALPHA' => '[A-Z]+',
		'#Alpha' => '[A-Za-z]+',
		'#alphanum' => '[a-zA-Z0-9]+',
		'#slug' => '[a-z0-9_.-]+',
	];
	static function get($key, $default = null) {
		return $_GET[$key] ?? $_POST[$key] ?? $_REQUEST[$key] ?? $default;
	}
	static function load() {
		self::$method = self::getMethod();
		self::$uri = trim($_SERVER['REQUEST_URI'], "/");
		self::$parts = explode('/', self::$uri);
	}
	static private function getMethod() {
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
	static function match($path) {
		$path = trim($path, '/');
		$path = preg_replace('~(#[a-z]+)(\??)~i', '($1)$2', $path);
		$path = str_replace(array_keys(self::$placeholders), array_values(self::$placeholders), $path);
		$match = preg_match("~^$path$~i", self::$uri, $matches);
		self::$parts = array_slice($matches, 1);
		return $match > 0;
	}
}
Request::load();
