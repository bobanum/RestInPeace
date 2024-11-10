<?php
namespace RestInPeace;

class Router {
	static function group($path, $callback) {
		$bak_parts = Request::$parts;
		$bak_uri = Request::$uri;
		$parts = [];
		$prefix = Request::matchStart($path, $parts);
		if ($prefix === false) {
			return;
		}
		Request::$uri = substr(Request::$uri, strlen($prefix));
		if (is_string($callback)) {
			$callback = [RestInPeace::class, $callback];
		}
		$result = call_user_func_array($callback, Request::$parts);
		Request::$parts = $bak_parts;
		Request::$uri = $bak_uri;
		return $result;
	}
	static function get($path, $callback = null) {
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method !== 'GET') {
			return;
		}
		if (Request::match($path) === false) {
			return;
		}
		if (empty($callback)) return true;

		if (is_string($callback)) {
			$callback = [RestInPeace::class, $callback];
		}
		return call_user_func_array($callback, Request::$parts);
	}
	static function post($path, $callback = null) {
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method !== 'POST') {
			return;
		}
		if (Request::match($path) === false) {
			return;
		}
		if (empty($callback)) return true;
		
		if (is_string($callback)) {
			$callback = [RestInPeace::class, $callback];
		}
		return call_user_func_array($callback, Request::$parts);
	}
}
