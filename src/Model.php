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
	function __construct($table, $id = null) {
		$this->table = $table;
		$this->id = $id;
	}
	/**
	 * Fetches a record by its ID.
	 *
	 * @param integer $id Optional. The ID of the record to fetch. If null, fetches all records.
	 * @return $this The model instance.
	 */
	function fetch($id = null) {
		$id = $id ?? $this->id;
		$query = [];
		$cols = "*";

		$query['SELECT'] = sprintf('%s FROM `%s`', $cols, $this->table_name);
		$query['WHERE'] = sprintf('`%s` = ?', $this->table_primary_key);
		$result = $this->table->execute($query, [$id]);
		if (count($result) === 0) {
			return $this;
		}
		$result = $this->excludeColumns($result[0]);
		$this->table->addHateoas($result);
		foreach ($result as $key => $value) {
			$this->attributes[$key] = $value;
		}
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
			if ($relation->type === Relation::BELONGS_TO) {
				foreach ($result[0] as $key => $value) {
					$this->attributes[$relation->name . '_' . $key] = $value;
				}
			} else {
				$this->attributes[$relation->name] = $result;
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
		$columns = array_flip([...self::$excluded, ...$columns, ]);
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
}
