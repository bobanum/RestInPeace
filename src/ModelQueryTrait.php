<?php

namespace RestInPeace;

trait ModelQueryTrait {
	static protected $tableName;
	public static function all() {
		$query = new Query(static::class);
		return $query->get();
	}
	public static function get($id) {
		$model = new static;
		$model->fetchWith();
		return $model->find($id);
	}
	public static function find($id) {
		$model = new static;
		$model->fetchWith();
		return $model->where('id', $id)->first();
	}
	public static function where($field, $value) {
		$model = new static;
		$model->fetchWith();
		return $model->where($field, $value);
	}
	public static function fetchWith() {
		$model = new static;
		if (isset($model->with)) {
			$model->with($model->with);
		}
		return $model;
	}
	public static function with($relations) {
		$model = new static;
		return $model->with($relations);
	}
	public static function first() {
		$query = new Query(static::class);
		return $query->limit(1);
	}
	public static function orderBy($field, $direction) {
		$query = new Query(static::class);
		return $query->orderBy($field, $direction);
	}
	public static function limit($limit) {
		$query = new Query(static::class);
		return $query->limit($limit);
	}
	public static function offset($offset) {
		$query = new Query(static::class);
		return $query->offset($offset);
	}
}
