<?php

namespace RestInPeace;

class Collection implements \Countable, \Iterator, \ArrayAccess {
	protected $items = [];
	protected $model;
	protected $key;
	function __construct($key = null, $model = null) {
		$this->model = $model;
		$this->key = $key;
	}
	function add(...$item) {
		foreach ($item as $i) {
			if ($this->key) {
				$this->items[$i->{$this->key}] = $i;
			} else {
				$this->items[] = $i;
			}
		}
	}
	function __call($name, $arguments) {
		$result = [];
		foreach ($this->items as $key => $item) {
			if (method_exists($item, $name)) {
				$result[$key] = $item->$name(...$arguments);
			} else {
				$result[$key] = null;
			}
		}
		return $result;
	}
	function get($key) {
		return $this->items[$key];
	}
	function all() {
		return $this->items;
	}
	function first() {
		return reset($this->items);
	}
	function last() {
		return end($this->items);
	}
	public function count(): int {
		return count($this->items);
	}
	public function current(): mixed {
		return current($this->items);
	}
	public function key(): mixed {
		return key($this->items);
	}
	public function next(): void {
		next($this->items);
	}
	public function rewind(): void {
		reset($this->items);
	}
	public function valid(): bool {
		return key($this->items) !== null;
	}
	public function offsetExists($offset): bool {
		return isset($this->items[$offset]);
	}
	public function offsetGet($offset): mixed {
		return $this->items[$offset];
	}
	public function offsetSet($offset, $value): void {
		if (is_null($offset)) {
			$this->items[] = $value;
		} else {
			$this->items[$offset] = $value;
		}
	}
	public function offsetUnset($offset): void {
		unset($this->items[$offset]);
	}
	public function map($callback) {
		return array_map($callback, $this->items);
	}
	public function filter($callback) {
		return array_filter($this->items, $callback);
	}
	public function filterProp($prop, $value) {
		$array = $this->filter(fn($item) => (is_array($item) ? $item[$prop] : $item->$prop) === $value);
		return $array;
	}
	public function find($callback) {
		$array = array_filter($this->items, $callback);
		return array_shift($array);
	}
	public function findProp($prop, $value) {
		return $this->find(fn($item) => (is_array($item) ? $item[$prop] : $item->$prop) === $value);
	}

	public function pluck($property, $key = null) {
		$key = $key ?? $this->key;
		$result = [];
		foreach ($this->items as $item) {
			if (is_array($property)) {
				$prop = array_map(fn($p) => $item->$p ?? $item[$p], $property);
			} else {
				$prop = $item->$property ?? $item[$property];
			}
			if ($key) {
				$result[$item->$key] = $prop;
			} else {
				$result[] = $prop;
			}
		}
		return $result;
	}
	function __toArray() {
		vd($this->items);
		return [...$this->items];
	}
	function toArray() {
		return $this->__call('toArray', []);
	}
	function pop() {
		return array_pop($this->items);
	}
	function shift() {
		return array_shift($this->items);
	}
	function push($item, $key = null) {
		if ($key) {
			$this->items[$key] = $item;
		} else {
			$this->items[] = $item;
		}
	}
	function unshift($item, $key = null) {
		if ($key) {
			$this->items = [$key => $item, ...$this->items];
		} else {
			array_unshift($this->items, $item);
		}
	}
	function merge(...$collections) {
		foreach ($collections as $collection) {
			$this->items = [...$this->items, ...($collection->items ?? $collection)];
		}
		return $this;
	}
}
