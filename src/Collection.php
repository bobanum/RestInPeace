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
class Collection extends ArrayObject {
	use CollectionAccessorsTrait;
	public function __call($func, $argv) {
		if (substr($func, 0, 6) !== 'array_') {
			$func = 'array_' . $func;
		}
		if (!is_callable($func)) {
			throw new \BadMethodCallException(__CLASS__ . '->' . $func);
		}
		$array = $this->getArrayCopy();
		$result = $func($array, ...$argv);
		$this->exchangeArray($array);
		return $result;
	}

	public function map($callback, ...$args) {
		$array = $this->getArrayCopy();
		$result = array_combine(array_keys($array), array_map($callback, $array, array_keys($array), ...$args));
		return new self($result);
	}
	public function walk($callback, ...$args) {
		$this->exchangeArray([...$this->map($callback, ...$args)]);
		return $this;
	}
	public function setKeys($keys) {
		if (is_callable($keys)) {
			$keys = $this->map($keys);
		}
		if ($keys instanceof self) {
			$keys = $keys->getArrayCopy();
		}
		$array = $this->getArrayCopy();
		$result = array_combine($keys, array_values($array));

		$this->exchangeArray($result);
		return $this;
	}

	public function attrToKeys($name = 'id') {
		$this->setKeys(fn($item, $key) => $item->$name ?? $key);
	}

	public function filter($callback = null) {
		$array = $this->getArrayCopy();
		$result = array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
		$this->exchangeArray($result);
		return $this;
	}
	public function filterKeys($callback) {
		$array = $this->getArrayCopy();
		$result = array_filter($array, $callback, ARRAY_FILTER_USE_KEY);
		$this->exchangeArray($result);
		return $this;
	}
	public function clone() {
		return clone $this;
	}
	public function __toString() {
		return json_encode($this->getArrayCopy());
	}
	public function toArray() {
		return $this->getArrayCopy();
	}
	public function pluck($key) {
		$array = $this->getArrayCopy();
		if (is_callable($key)) {
			return array_map($key, $array);
		}
		if (is_array($key)) {
			return array_map(fn($item) => array_intersect_key($item, array_flip($key)), $array);
		}
		if (is_object($key)) {
			$keys = (array)$key;
			return array_map(fn($item) => (object)array_intersect_key($item, $keys), $array);
		}
		return $this->map(fn($item) => $item[$key] ?? null);
	}
}
