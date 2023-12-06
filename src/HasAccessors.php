<?php

namespace RestInPeace;

trait HasAccessors {
	static $attributes = [];

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
}
