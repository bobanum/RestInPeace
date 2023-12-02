<?php

namespace RestInPeace;

class Response {
	static $options = ['UNESCAPED_SLASHES', 'UNESCAPED_UNICODE'];
	static $headers = [
		'Access-Control-Allow-Origin' => '*',
		'Access-Control-Expose-Headers' => 'x-http-method-override',
		'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE',
		'Content-Type' => 'application/%s; charset=utf-8',
	];
	public static $HTTP = [
		200 => [
			'status' => 'success',
			'code' => 200,
			'message' => 'OK',

		],
		201 => [
			'status' => 'success',
			'code' => 201,
			'message' => 'Created',
		],
		204 => [
			'status' => 'error',
			'code' => 204,
			'message' => 'No Content',
		],
		400 => [
			'status' => 'error',
			'code' => 400,
			'message' => 'Bad Request',
		],
		403 => [
			'status' => 'error',
			'code' => 403,
			'message' => 'Forbidden',
		],
		404 => [
			'status' => 'error',
			'code' => 404,
			'message' => 'Not Found',
		],
		409 => [
			'status' => 'error',
			'code' => 409,
			'message' => 'Conflict',
		],
		503 => [
			'status' => 'error',
			'code' => 503,
			'message' => 'Service Unavailable',
		],
	];
	public $status;
	public $code;
	public $message;
	public $content;
	public function __construct() {
		$this->status = 'success';
		$this->code = 200;
		$this->message = 'OK';
		$this->content = null;
	}
	public static function reply($data, $http_code = null) {
		$bitmask = 0;
		$options = self::$options;
		
		if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			$options[] = 'PRETTY_PRINT';
		}
		
		foreach ($options as $option) {
			$bitmask |= defined('JSON_' . $option) ? constant('JSON_' . $option) : 0;
		}
		$result = json_encode($data, $bitmask);
		
		if ($result === false) return false;
		
		// TODO Check if relevant
		$callback = null;
		
		if (!empty($_GET['callback'])) {
			$callback = trim(preg_replace('~[^[:alnum:]\[\]_.]~', '', $_GET['callback']));
			
			if (!empty($callback)) {
				$result = sprintf('%s(%s);', $callback, $result);
			}
		}
		
		if (!headers_sent()) {
			self::headers(['Content-Type' => empty($callback) ? 'json' : 'javascript']);
			if (!empty($http_code)) {
				http_response_code($http_code);
			} else if (array_key_exists('status', $data) && array_key_exists('code', $data)) {
				http_response_code($data['code']);
			}
		}
		exit($result);
	}
	static function headers($data = []) {
		foreach (self::$headers as $name => $val) {
			if (array_key_exists($name, $data)) {
				$datum = $data[$name];
				if (!is_array($datum)) {
					$datum = [$datum];
				}
				$val = sprintf($val, ...$datum);
			}
			$header = sprintf('%s: %s', $name, $val);
			header($header);
		}
	}
}
