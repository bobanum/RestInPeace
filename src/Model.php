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
class Model {
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
	function __construct($table = null, $id = null, $data = []) {
		$this->table = $table;
	}
	function fill($data) {
		foreach ($data as $key => $value) {
			$this->attributes[$key] = $value;
		}
	}
	function getSelect($condition = '= ?') {
		if (is_numeric($condition) && $condition > 1) {
			$condition = 'in (' . implode(',', array_fill(0, $condition, '?')) . ')';
		}
		$query = [];
		$cols = "*";

		$query['SELECT'] = sprintf('%s FROM `%s`', $cols, $this->table_name);
		$query['WHERE'] = sprintf('`%s` %s', $this->table_primary_key, $condition);
		return $query;
	}
	/**
	 * Fetches a record by its ID.
	 *
	 * @param integer $id Optional. The ID of the record to fetch. If null, fetches all records.
	 * @return $this The model instance.
	 */
	function fetch($id = null) {
		if (is_null($id)) {
			$id = [$this->id];
		} else if (!is_array($id)) {
			$id = [$id];
		}
		$query = $this->getSelect(count($id));

		$result = $this->table->execute($query, $id);
		if (count($result) === 0) {
			return $this;
		}
		$result = $this->excludeColumns($result[0]);
		$this->fill($result);
		return $this;
	}
	/**
	 * Fetches related models based on the provided relation names and adds them to the model instance.
	 *
	 * @param string[] $relationNames The names of the relations to fetch.
	 * @return $this
	 */
	function fetchRelated(...$relationNames) {
		if (empty($relationNames)) {
			$relations = $this->table_relations;
		} else {
			$relations = array_intersect_key($this->table_relations, array_flip($relationNames));
		}

		foreach ($relations as $relation) {
			$exclude_r = [$relation->foreign_key, ...self::$excluded];
			$query = [$relation->getSelect()];
			$result = $this->table->execute($query, $this->id);
			$result = array_map(fn($row) => $this->excludeColumns($row, $exclude_r), $result);
			array_walk($result, fn(&$row) => $this->table->addRelatedHateoas($relation, $row));
			if (count($result) === 0 && $relation->type === Relation::BELONGS_TO) {
				$this->attributes[$relation->name] = null;
			} else if (count($result) === 0) {
				$this->attributes[$relation->name] = [];
			} else if ($relation->type === Relation::BELONGS_TO) {
				$result = $result[0];
				// unset($this->attributes[$relation->foreign_key]);
				// unset($result[$this->table->get_foreign_key()]);
				$this->attributes[$relation->name] = $result;
			} else {
				$this->attributes[$relation->name] = $result;
			}
		}
		return $this;
	}
	function fetchWith($with = null) {
		if ($with === null) {
			$with = $this->with;
		}
		foreach ($with as $relationName) {
			$names = explode('.', $relationName, 2);
			$r = array_shift($names);
			$relation = $this->table->relations[$r];
			if (!isset($this->attributes[$r])) {
				$this->attributes[$r] = $relation->fetch($this->id, $names);
			} else if (count($names) > 0) {
				$relation->fetchWith($this->attributes[$r], $names);
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
		$columns = array_flip([...self::$excluded, ...$columns,]);
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
		if (method_exists($this, $get_name)) {
			return $this . $get_name();
		}
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		if (substr($name, 0, 6) === "table_") {
			$name = substr($name, 6);
			return $this->table->$name;
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
	function save() {
		$query = [];
		$columnNames = array_keys($this->table->columns);
		$columnNames = array_diff($columnNames, self::$excluded);
		$pk = $this->table_primary_key;
		$values = array_intersect_key($this->attributes, array_flip($columnNames));
		unset($values[$pk]);
		if ($this->$pk) {
			$cols = array_map(fn($col) => "`$col` = ?", array_keys($values));
			$cols = implode(", ", $cols);
			$values[$pk] = $this->$pk;
			$query['UPDATE'] = sprintf('`%s` SET %s WHERE `%s` = ?', $this->table_name, $cols, $this->table_primary_key);
		} else {
			$cols = array_map(fn($col) => "`$col`", array_keys($values));
			$cols = implode(", ", $cols);
			$vals = array_fill(0, count($values), "?");
			$vals = implode(", ", $vals);

			$query['INSERT INTO'] = sprintf('`%s` (%s) VALUES (%s)', $this->table_name, $cols, $vals);
		}
		// vd($query, $values);
		$result = $this->table->execute($query, $values);
		return $result;
	}
	function hasOne($table, $foreign_key) {
	}
	function hasMany($table, $foreign_key) {
	}
	function belongsTo($table, $foreign_key) {
	}
	function belongsToMany($table, $foreign_key) {
	}
	function belongsToThrough($table, $foreign_key) {
	}
	function hasManyThrough($table, $foreign_key) {
	}
	function toArray() {
		$excluded = [
			...self::$excluded,
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
		return $result;
	}
}
