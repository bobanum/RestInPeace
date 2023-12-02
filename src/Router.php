<?php
namespace RestInPeace;

class Router {
	public function __construct() {
		echo "Hello World!";
	}
	static function get($path, $callback = null) {
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method !== 'GET') {
			return;
		}
		if (!Request::match($path)) {
			return;
		}
		if (empty($callback)) return true;

		if (is_string($callback)) {
			$callback = [RestInPeace::class, $callback];
		}
		Response::reply(call_user_func_array($callback, Request::$parts));
	}
}
