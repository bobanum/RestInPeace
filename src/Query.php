<?php

namespace RestInPeace;

class Query {
	public $table;
	public $modelClass;
	public $_columns = ['*'];
	public $_where = [];
	public $_limit;
	public $_offset;
	public $_orderBy = [];
	public $_data = [];
	public function __construct($table) {
		if (is_object($table)) {
			$this->modelClass = get_class($table);
			$this->table = $table::getTable();
		} else if (is_string($table) && class_exists($table)) {
			$this->modelClass = $table;
			$this->table = $table::getTable();
		} else {
			$this->table = $table;
			$this->modelClass = Model::findModel($table);
		}
	}
	public function __toString() {
		return $this->normalizeQuery($this->toArray());
	}
	/**
	 * Normalize the given SQL query.
	 *
	 * This method takes a raw SQL query and normalizes it to ensure it adheres to
	 * the expected format or standards. This can include tasks such as trimming
	 * whitespace, converting keywords to uppercase, or other formatting adjustments.
	 *
	 * @param string[]|string $query The raw SQL query to be normalized.
	 * @return string The normalized SQL query.
	 */
	public function normalizeQuery($query) {
		if (is_string($query)) return $query;
		$query = array_map(function ($key, $item) {
			if (is_numeric($key)) {
				return $item;
			}
			if ($key === 'WHERE') {
				return $key . ' ' . self::normalizeWhere($item);
			}
			if (is_array($item)) {
				$item = implode(',', $item);
			}
			return $key . ' ' . $item;
		}, array_keys($query), $query);
		return implode(' ', $query);
	}
	static public function normalizeColumns($columns) {
		if (empty($columns)) return '*';
		$result = [];
		foreach ($columns as $alias => $column) {
			if (is_numeric($alias)) {
				$result[] = $column;
			} else {
				$result[] = "$column AS $alias";
			}
		}
		return implode(', ', $result);
	}
	/**
	 * Normalize the given where clause.
	 *
	 * @param mixed $where The where clause to normalize. This can be an array, string, or other type.
	 * @param int $op Optional. The operation type. Default is 0.
	 * @return mixed The normalized where clause.
	 */
	static public function normalizeWhere($where, $op = 0) {
		if (is_string($where)) return $where;

		$ops = [' AND ', ' OR ',];

		$where = array_map(function ($item) use ($op) {
			if (is_string($item)) return $item;
			return self::normalizeWhere($item, 1 - $op);
		}, $where);
		return sprintf("(%s)", implode($ops[$op], $where));
	}
	static public function normalizeOrderBy($orderBy) {
		$result = [];
		foreach ($orderBy as $column => $direction) {
			if (is_numeric($column)) {
				$result[] = "$direction";
			} else {
				$result[] = "$column $direction";
			}
		}
		return implode(', ', $result);
	}
	public function toArray() {
		$result = [];
		$result['SELECT'] = $this->normalizeColumns($this->_columns);
		$result['FROM'] = "`{$this->table}`";
		if (!empty($this->_where)) {
			$result['WHERE'] = $this->normalizeWhere($this->_where);
		}
		if (!empty($this->_orderBy)) {
			$result['ORDER BY'] = $this->normalizeOrderBy($this->_orderBy);
		}
		if (!empty($this->_limit)) {
			$result['LIMIT'] = $this->_limit;
		}
		if (!empty($this->_offset)) {
			if (empty($this->_limit)) {
				$result['LIMIT'] = 1;
			}
			$result['OFFSET'] = $this->_offset;
		}
		return $result;
	}
	public function select($columns) {
		if (!is_countable($columns)) {
			$columns = [$columns];
		}
		$this->_columns = $columns;
		return $this;
	}
	public function where($column, $value, $operator = '=') {
		if ($value instanceof Query) {
			foreach ($value->_data as $key => $val) {
				$this->_data[$key] = $val;
			}
			$value = "($value)";
			$this->_where[] = "`$column` $operator {$value}";
		} else {
			$this->_data[$column] = $value;
			$this->_where[] = "`$column` $operator :{$column}";
		}
		return $this;
	}
	public function limit($limit) {
		$this->_limit = $limit;
		return $this;
	}
	public function offset($offset) {
		$this->_offset = $offset;
		return $this;
	}
	public function orderBy($column, $direction = 'ASC') {
		$this->_orderBy[] = "$column $direction";
		return $this;
	}
	public function get($select = []) {
		if (!empty($select)) {
			$this->select($select);
		}
		return $this->execute();
	}
	/**
	 * Adds parameters to the query array.
	 *
	 * This function allows you to add parameters to the query array by passing a reference to the array and the source of the parameters.
	 *
	 * @param array $query The query array to which the parameters will be added.
	 * @param mixed $source The source of the parameters. This can be an array, an object, or null.
	 * @return void
	 */
	function addParams($source = null) {
		$source = $source ?? $_GET;
		if (isset($source['by'])) {
			if (isset($source['order'])) {
				$this->orderBy($source['by'], $source['order']);
			} else {
				$this->orderBy($source['by']);
			}
		}

		if (isset($source['limit'])) {
			$this->limit($source['limit']);
		} 
		if (isset($source['offset'])) {
			$this->limit($source['offset']);
		}
		return $this;
	}

    /**
     * Retrieves the columns of the table or view.
     *
     * @param bool $implode Optional. If true, the columns will be returned as a comma-separated string. 
     *                      If false, the columns will be returned as an array. Default is true.
     * @return string|array The columns of the table or view, either as a comma-separated string or an array.
     */
    public function getCols() {
        if (empty($_GET['cols'])) return $this;
        $cols = explode(",", $_GET['cols']);
        $cols = array_map(fn($col) => htmlspecialchars($col), $cols);
        if (count($cols)) {
			$this->select($cols);
		}
        return $this;
    }

	/**
	 * Retrieve the first result of the query.
	 *
	 * @return Model The first result of the query, or null if no results are found.
	 */
	public function first() {
		$this->limit(1);
		$result = $this->execute();
		// $db = RestInPeace::connect();
		// $result = $db->executeClass($this->modelClass, "$this", $this->_data);
		return $result[0] ?? null;
	}
	public function last() {
		$this->offset("(select count(*)-1 from `{$this->table}`)");
		return $this->first();
	}
	public function find($id) {
		$this->where('id', $id);
		return $this->first();
	}
	public function execute() {
		$db = RestInPeace::connect();
		$data = $this->_data;
		$query = "$this";
		try {
			$statement = $db->prepare($query);

			$statement->setFetchMode(\PDO::FETCH_CLASS, $this->modelClass);
			if (count($data, COUNT_RECURSIVE) > count($data)) {
				$data = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data)), false);
			}

			if ($statement->execute($data) === true) {
				$sequence = null;

				switch (strstr($query, ' ', true)) {
					case 'INSERT':
					case 'REPLACE':
						return $db->pdo->lastInsertId($sequence);

					case 'UPDATE':
					case 'DELETE':
						return $statement->rowCount();

					case 'SELECT':
					case 'EXPLAIN':
					case 'PRAGMA':
					case 'SHOW':
						return $db::fetch($statement);
				}
				return true;
			}
		} catch (\Exception $exception) {
			vdd($query, $data);
			throw new \Exception($exception->getMessage());

			vdj($exception->getMessage(), $query, $data);
			// return ['status' => 'error', 'message' => $exception->getMessage()];
		}
	}
}
