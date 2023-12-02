<?php
namespace RestInPeace;

class Config {
	function get($key, $default = null) {
		return $_ENV["RIP_{$key}"] ?? $_ENV[$key] ?? $default;
	}
}