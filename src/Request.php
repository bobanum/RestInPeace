<?php

namespace RestInPeace;

/**
 * Class Request
 *
 * This class is part of the RestInPeace library and is used to handle HTTP requests.
 * 
 * @package bobanum\restinpeace
 */
class Request {
	/** @var string|null $method The HTTP method for the request */
	static $method;
	/** @var string $uri Static variable to store the URI */
	static $uri;
	/** @var string[] $parts Static array to hold parts of the request */
	static $parts = [];
	/** @var array $placeholders Static array to hold placeholder values */
	static $placeholders = [
		'#any' => '[^/\?#]+',
		'#num' => '[0-9]+',
		'#alpha' => '[a-z]+',
		'#ALPHA' => '[A-Z]+',
		'#Alpha' => '[A-Za-z]+',
		'#alphanum' => '[a-zA-Z0-9]+',
		'#slug' => '[a-z0-9_\.\~\-]+',
	];
	/**
	 * Retrieve a value from the request using the specified key.
	 *
	 * @param string $key The key to look for in the request.
	 * @param mixed $default The default value to return if the key is not found. Default is null.
	 * @return mixed The value associated with the key, or the default value if the key is not found.
	 */
	static function get($key, $default = null) {
		return $_GET[$key] ?? $_POST[$key] ?? $_REQUEST[$key] ?? $default;
	}
	/**
	 * Loads the request data.
	 *
	 * This static function is responsible for loading the request data.
	 * It does not take any parameters and does not return any value.
	 *
	 * @return void
	 */
	static function load() {
		self::$method = self::getMethod();
		$folder = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/index.php'));
		$uri = preg_replace('~/+$~', '', $_SERVER['REQUEST_URI']); // remove trailing slash
		$uri = substr($uri, strlen($folder));
		self::$uri = $uri;
		return self::$uri;
	}
	/**
	 * Retrieves the HTTP request method.
	 *
	 * This static private function returns the HTTP request method used for the current request.
	 *
	 * @return string The HTTP request method (e.g., 'GET', 'POST', 'PUT', 'DELETE').
	 */
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
	/**
	 * Matches the given path against predefined criteria.
	 *
	 * @param string $path The path to be matched.
	 * @return mixed The result of the match operation.
	 */
	static function match($path) {
		$pattern = trim($path, '/');
		$pattern = preg_replace('~(#[a-z]+)(\??)~i', '($1)$2', $pattern);
		$pattern = str_replace(array_keys(self::$placeholders), array_values(self::$placeholders), $pattern);
		$pattern = sprintf('~^/?%s$~i', $pattern);
		$match = preg_match($pattern, self::$uri, $matches);
		if (!$match) return false;
		array_push(self::$parts, ...array_slice($matches, 1));
		return $matches[0];
	}
	/**
	 * Matches the beginning of the given path.
	 *
	 * @param string $path The path to be matched.
	 * @return bool Returns true if the path matches the start condition, false otherwise.
	 */
	static function matchStart($path) {
		$pattern = $path;
		$pattern = preg_replace('~(#[a-z]+)(\??)~i', '($1)$2', $pattern);
		$pattern = str_replace(array_keys(self::$placeholders), array_values(self::$placeholders), $pattern);
		$pattern = sprintf('~^/?%s~i', $pattern);
		$match = preg_match($pattern, self::$uri, $matches);
		if (!$match) return false;
		array_push(self::$parts, ...array_slice($matches, 1));
		return $matches[0];
	}
}
Request::load();
