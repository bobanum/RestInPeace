<?php

namespace RestInPeace;

class Response {
	static $json_options = ['UNESCAPED_SLASHES', 'UNESCAPED_UNICODE'];
	static $headers = [
		'Access-Control-Allow-Origin' => '*',
		'Access-Control-Expose-Headers' => 'x-http-method-override',
		'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE',
		'Content-Type' => '%s; charset=utf-8',
	];
	public static $HTTP = [
		200 => [
			'status' => 'Success',
			'code' => 200,
			'message' => 'OK',

		],
		201 => [
			'status' => 'Success',
			'code' => 201,
			'message' => 'Created',
		],
		204 => [
			'status' => 'Error',
			'code' => 204,
			'message' => 'No Content',
		],
		400 => [
			'status' => 'Error',
			'code' => 400,
			'message' => 'Bad Request',
		],
		403 => [
			'status' => 'Error',
			'code' => 403,
			'message' => 'Forbidden',
		],
		404 => [
			'status' => 'Error',
			'code' => 404,
			'message' => 'Not Found',
		],
		409 => [
			'status' => 'Error',
			'code' => 409,
			'message' => 'Conflict',
		],
		503 => [
			'status' => 'Error',
			'code' => 503,
			'message' => 'Service Unavailable',
		],
	];
	private $_code;
	public $status;
	public $message;
	public $content;
	public $options;
	public $contentType;
	public function __construct($content = null, $code = null) {
		$this->content = $content;
		if (is_array($content) && array_key_exists('status', $content) && array_key_exists('code', $content)) {
			$code = $code ?? $content['code'];
		}
		$this->code = $code ?? 200;

		$this->contentType = 'application/json';
		$this->options = self::$json_options;
	}
	public function __get($name) {
		$get_name = 'get_' . $name;
		if (method_exists($this, $get_name)) {
			return $this->$get_name();
		}
	}
	public function __set($name, $value) {
		$set_name = 'set_' . $name;
		if (method_exists($this, $set_name)) {
			return $this->$set_name($value);
		}
	}
	public function get_code() {
		return $this->_code;
	}
	public function set_code($value) {
		$this->_code = $value;
		$http = self::$HTTP[$value] ?? null;
		if (!empty($http)) {
			$this->status = $http['status'];
			$this->message = $http['message'];
		} else {
			$this->status = 'error';
			$this->message = '';
		}
	}
	public static function reply($data, $http_code = null) {
		if ($data instanceof self) {
			$result = $data;
			if (!empty($http_code)) {
				$result->code = $http_code;
			}
		} else {
			$result = new self($data, $http_code);
		}

		$result->send();
	}
	public static function sendCode($code, $message = null) {
		$result = self::$HTTP[$code] ?? [
			'status' => 'Error',
			'code' => $code,
			'message' => 'Unknown',
		];

		$result = new self($result);
		$result->send();
	}
	public function send() {

		$result = $this->toJson();

		if ($result === false) return false;

		if (!headers_sent()) {
			http_response_code($this->code);
			self::headers(['Content-Type' => $this->contentType]);
		}
		exit($result);
	}

	static public function json_encode($content = null, $options = []) {
		$bitmask = 0;

		if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			$options[] = 'PRETTY_PRINT';
		}

		foreach ($options as $option) {
			$bitmask |= defined('JSON_' . $option) ? constant('JSON_' . $option) : 0;
		}
		$result = json_encode($content, $bitmask);
		return $result;
	}
	public function __toString() {
		return $this->toJson();
	}
	public function toJson($content = null, $options = []) {
		$content = $content ?? $this->content;
		$options = $this->options + $options;

		return self::json_encode($content, $options);
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
