<?php

namespace RestInPeace;

class Request {
	static $method;
	static $uri;
	static $parts = [];
	static $placeholders = [
		'#any' => '[^/]++',
		'#num' => '[0-9]++',
		'#alpha' => '[a-z]++',
		'#ALPHA' => '[A-Z]++',
		'#Alpha' => '[A-Za-z]++',
		'#alphanum' => '[a-zA-Z0-9]++',
		'#slug' => '[a-z0-9_.-]++',
	];
	static function load() {
		self::$method = $_SERVER['REQUEST_METHOD'];
		self::$uri = $_SERVER['REQUEST_URI'];
		self::$parts = explode('/', self::$uri);
	}
	static function match($path) {
		// $path = preg_replace('~/\(#[a-z]+\)\?~i', '(?:$0)?', $path);
		$path = str_replace(array_keys(self::$placeholders), array_values(self::$placeholders), $path);
	}
}
