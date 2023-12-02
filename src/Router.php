<?php
namespace RestInPeace;

class Router {
	public function __construct() {
		echo "Hello World!";
	}
	static function get($path, $callback) {
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method !== 'GET') {
			return;
		}
		$uri = $_SERVER['REQUEST_URI'];
		$uri = parse_url($uri, PHP_URL_PATH);
		if ($uri !== $path) {
			return;
		}
		$callback();
	}
}