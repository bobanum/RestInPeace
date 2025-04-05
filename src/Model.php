<?php

namespace RestInPeace;

/**
 * Class Model
 *
 * This class is part of the RestInPeace library and is located in the src directory.
 * It serves as a base model for interacting with data within the application.
 *
 * @package bobanum\restinpeace
 */
abstract class Model {
	use ModelQueryTrait;

	static protected $tableName;
	static protected $primaryKeys;
	static protected $_attributes;
	/**
	 * @var array $excluded List of fields to be excluded.
	 * 
	 * This static property holds an array of field names that should be excluded.
	 * Currently, it includes 'created_at' and 'updated_at'. 
	 * TODO: Make this list configurable.
	 */
	static $excluded = ['created_at', 'updated_at'];
	/** @var array $attributes An array to store the attributes of the model. */
	public $attributes = [];
	/** @var TableOrView $table The name of the database table associated with the model. */
	public $table;
	/**
	 * Constructor for the Model class.
	 *
	 * @param TableOrView $table The table or view associated with the model.
	 * @param integer $id Optional. The ID of the record to be loaded. Default is null.
	 */
	function __construct($table = null) {
		$this->table = $table;
	}

	function fill($data) {
		foreach ($data as $key => $value) {
			$this->attributes[$key] = $value;
		}
		return $this;
	}
	static function find($id, $deep = false) {
		$query = new Query(static::class);
		$query->where('id', $id);
		$result = $query->first();
		if ($deep) {
			$result->fetchWith();
		}
		return $result;
	}
	static function get_primaryKey() {
		return implode('-', static::$primaryKeys);
	}

	static function getTable() {
		return static::$tableName;
	}
	static function findModel($modelName) {
		if (is_object($modelName)) return get_class($modelName);
		if (class_exists($modelName)) return $modelName;
		$class = __NAMESPACE__ . '\\Models\\' . $modelName;
		if (class_exists($class)) return $class;
		$class = __NAMESPACE__ . '\\Models\\' . ucfirst($modelName);
		if (class_exists($class)) return $class;
		$class = substr($modelName, 0, -1);
		if (class_exists($class)) return $class;
		return $class;
	}
	/**
	 * Fetches related models based on the provided relation names and adds them to the model instance.
	 *
	 * @param string[] $relationNames The names of the relations to fetch.
	 * @return $this
	 */
	function fetchWith($with = null) {
		$with = $with ?? $this->with;
		if (!is_array($with)) {
			$with = [$with];
		}
		foreach ($with as $alias => $relationName) {
			if (isset($this->with[$relationName]) && !in_array($relationName, $this->with)) {
				$alias = $relationName;
				$relationName = $this->with[$relationName];
			}
			$names = explode('.', $relationName, 2);
			$r = array_shift($names);
			if (is_numeric($alias)) {
				$alias = $r;
			}

			$value = $this->$r;
			if (!empty($value)) {
				$this->attributes[$alias] = $value;
				if (count($names) > 0) {
					$value->fetchWith($names); // Uses a Collection to Iterate
				}
			}
		}
		return $this;
	}
	/**
	 * Filters the given row to keep only the specified columns.
	 *
	 * @param array $row The associative array representing a row of data.
	 * @param string[] $columns The list of column names to keep in the row.
	 * @return array The filtered row containing only the specified columns.
	 */
	function keepColumns($row, $columns) {
		$columns = array_flip($columns);
		$row = array_intersect_key($row, $columns);
		return $row;
	}

	/**
	 * Excludes specified columns from a given row.
	 *
	 * @param array $row The row from which columns should be excluded.
	 * @param array $columns An array of column names to be excluded from the row.
	 * @return array The row with the specified columns excluded.
	 */
	function excludeColumns($row, $columns = []) {
		$columns = array_flip([...static::$excluded, ...$columns,]);
		$row = array_diff_key($row, $columns);
		return $row;
	}
	/**
	 * Magic method to get the value of a property.
	 *
	 * This method is called when trying to access a property that is not 
	 * directly accessible or does not exist. It allows for dynamic retrieval 
	 * of properties.
	 *
	 * @param string $name The name of the property being accessed.
	 * @return mixed The value of the property, if it exists.
	 */
	function __get($name) {
		$get_name = "get_" . $name;
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		if (method_exists($this, $get_name)) {
			return $this->$get_name();
		}
		if (substr($name, 0, 6) === "table_") {
			$name = substr($name, 6);
			return $this->tableName;
		}
	}
	/**
	 * Magic method to set the value of a property.
	 *
	 * @param string $name The name of the property.
	 * @param mixed $value The value to set for the property.
	 */
	function __set($name, $value) {
		$set_name = "set_" . $name;
		if (method_exists($this, $set_name)) {
			return $this . $set_name($value);
		}
		$this->attributes[$name] = $value;
	}
	function __isset($name) {
		return isset($this->attributes[$name]) || method_exists($this, 'get_' . $name);
	}
	function save() {
		$query = [];
		$columnNames = array_keys(static::$_attributes);
		$columnNames = array_diff($columnNames, static::$excluded);
		$pk = $this->primaryKey;
		$values = array_intersect_key($this->attributes, array_flip($columnNames));
		unset($values[$pk]);
		if ($this->$pk) {
			$cols = array_map(fn($col) => "`$col` = :$col", array_keys($values));
			$cols = implode(", ", $cols);
			$values[$pk] = $this->$pk;
			$query['UPDATE'] = sprintf('`%s` SET %s WHERE `%s` = :%3$s', static::$tableName, $cols, static::get_primaryKey());
			$db = RestInPeace::connect();
			$db->execute($query, $values);
			return $this;
		} else {
			$cols = array_map(fn($col) => "`$col`", array_keys($values));
			$cols = implode(", ", $cols);
			$vals = array_map(fn($col) => ":$col", array_keys($values));
			$vals = implode(", ", $vals);
			$query['INSERT INTO'] = sprintf('`%s` (%s) VALUES (%s)', static::$tableName, $cols, $vals);
			$db = RestInPeace::connect();
			$db->execute($query, $values);
			return $this;
		}
	}
	function delete() {
		$query = [];
		$pk = $this->primaryKey;
		$query['DELETE FROM'] = sprintf('`%s` WHERE `%s` = ?', static::$tableName, static::get_primaryKey());
		$values = [$this->$pk];
		$db = RestInPeace::connect();
		$result = $db->execute($query, $values);
		return $result;
	}
	function mergeModel($model) {
		$attributes = &$model->attributes;
		unset($attributes['id']);
		foreach ($attributes as $key => $value) {
			if (!isset($this->attributes[$key])) {
				$this->attributes[$key] = $value;
				unset($attributes[$key]);
			}
		}
		return $this;
	}
	function hasOne($table, $foreign_key) {
		$query = new Query($table);
		$query->where($foreign_key, $this->id);
		return $query->first();
	}
	function hasMany($table, $foreign_key) {
		$query = new Query($table);
		$query->where($foreign_key, $this->id);
		return $query->get();
	}
	function belongsTo($table, $foreign_key) {
		$query = new Query($table);
		$sub = (new Query($this))->select($foreign_key)->where("id", $this->id);
		$query->where("id", $sub);

		$result = $query->first();
		if ($this->mergeOnes) {
			$this->mergeModel($result);
			return;
		}
		return $result;
	}
	function belongsToMany($table, $foreign_key, $pivot_table, $local_key) {
		$query = new Query($pivot_table);
		$query->select(["`{$table}`.*"]);
		$query->join($table, $foreign_key);
		$query->where("{$local_key}", $this->id);
		return $query->get();
	}

	function belongsToThrough($table, $foreign_key) {
	}
	function hasManyThrough($table, $foreign_key) {
	}
	function toArray() {
		// vd($this);
		$excluded = [
			...static::$excluded,
			// ...array_values(array_map(fn($relation) => $relation->foreign_key, $this->table->relations)),
		];
		$attributes = array_filter($this->attributes, fn($key) => !in_array($key, $excluded), ARRAY_FILTER_USE_KEY);

		$result = [];
		foreach ($attributes as $key => $value) {
			if ($value === null) {
				continue;
			} else if (is_object($value) && method_exists($value, 'toArray')) {
				$value = $value->toArray();
			} else if (is_array($value)) {
				$value = array_map(fn($v) => is_object($v) && method_exists($v, 'toArray') ? $v->toArray() : $v, $value);
			}
			$result[$key] = $value;
		}
		$result['url'] = RestInPeace::url($this::$tableName, $this->id);
		return $result;
	}
	static function __callStatic($name, $arguments) {
		if (method_exists(Query::class, $name)) {
			$query = new Query(static::class);
			return call_user_func_array([$query, $name], $arguments);
		}
	}
	static function where($column, $value, $operator = '=') {
		$query = new Query(static::class);
		$query->where($column, $value, $operator);
		return $query;
	}
}
