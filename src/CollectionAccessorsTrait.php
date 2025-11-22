<?php

namespace RestInPeace;

use ArrayObject;

/**
 * Class Collection
 * 
 * This class extends the ArrayObject class and represents a collection in the database.
 * 
 * @package bobanum\restinpeace
 */
trait CollectionAccessorsTrait {
	public function __get($name) {
		$name = "get_" . $name;
		if (method_exists($this, $name)) {
			return $this->$name();
		}
	}
	public function __set($name, $value) {
		$name = "set_" . $name;
		if (method_exists($this, $name)) {
			return $this->$name($value);
		}
	}

	public function get_length() {
		return $this->count();
	}
	
	public function get_keys() {
		return array_keys($this->getArrayCopy());
	}
	
	public function set_keys($keys) {
		$this->setKeys($keys);
		return $this;
	}
	
	public function get_values() {
		return array_values($this->getArrayCopy());
	}

}
