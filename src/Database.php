<?php

namespace RestInPeace;

abstract class Database {
	use HasAccessors;
	static protected $primary_key_pattern = '^id$';	// A regex pattern to match primary keys
	static protected $foreign_key_pattern = '^([a-z0-9_]+)_id$';	// A regex pattern to match primary keys
	private $_pdo;
	private $statements = [];
	public $schema = [];
	private $tables = null;
	public $views = null;
	public function __construct() {
		$this->_pdo = $this->newPDO();
	}
	public static $connectionOptions = [
		\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
		\PDO::ATTR_STRINGIFY_FETCHES => false,
	];
	function get_pdo() {
		if (!empty($this->_pdo)) {
			return $this->_pdo;
		}
		return $this->_pdo = $this->newPDO();
	}
	abstract function newPDO($options = []);
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
	static public function normalizeWhere($where, $op = 0) {
		if (is_string($where)) return $where;
		
		$ops = [' AND ', ' OR ', ];
		
		$where = array_map(function ($item) use ($op) {
			if (is_string($item)) return $item;
			return self::normalizeWhere($item, 1 - $op);
		}, $where);
		return sprintf("(%s)", implode($ops[$op], $where));
	}
	static public function findSuffixe($viewName, $tableName = '') {
		$regex = sprintf("~^%s__([a-z]+)$~", $tableName);
		if (preg_match($regex, $viewName, $matches)) {
			return $matches[1];
		}
		return false;
	}
	public function analyse() {
		$tables = $this->getTables();
		$views = $this->getViews();
		foreach ($tables as $key=>&$table) {
			if (empty($views)) break;	// If we just removed the last view
			$table->processSuffixedViews($views);
		}
		// Reanalyse to complete foreign keys and foreign tables
		foreach ($tables as $tableName=>$table) {
			foreach ($table->foreign_keys as $fk) {
				$foreignTable = $fk['table'];
				$table->addRelation($tables[$foreignTable], $fk['from']);
			}
			// Check for unprocessed foreign keys
			foreach ($table->columns as $columnName=>$column) {
				preg_match("~".self::$foreign_key_pattern."~", $column['name'], $matches);
				if (empty($matches)) continue;
				$relationName = $matches[1];
				if (isset($table->relations[$relationName])) continue;
				$pattern = sprintf("~^%s([^_]*)$~", preg_quote($foreignTable));
				$ft = array_filter(array_keys($tables), fn($name) => preg_match($pattern, $name));
				if (count($ft) === 0) continue;
				if (count($ft) > 1) {
					// Keep the shortest one
					$ft = array_reduce($ft, function ($carry, $item) {
						if (empty($carry)) return $item;
						if (strlen($item) < strlen($carry)) return $item;
						return $carry;
					});
				} else {
					$ft = array_pop($ft);
				}
				$table->addRelation($tables[$ft], $column['name']);
			}
		}
		return [
			"tables" => $tables,
			"views" => $views,
		];
	}
	abstract public function getTables();
	abstract public function getViews();
	abstract public function getColumns($table);
	abstract public function getIndexes($table);
	abstract public function getPrimaryKey($table);
	abstract public function getForeignKeys($table);

	public function prepare($query) {
		$hash = crc32($query);
		if (empty($this->statements[$hash])) {
			$this->statements[$hash] = $this->pdo->prepare($query);
		}
		return $this->statements[$hash];
	}
	public function execute($query, ...$data) {
		$query = $this->normalizeQuery($query);
		try {
			$statement = $this->prepare($query);
			if (count($data, COUNT_RECURSIVE) > count($data)) {
				$data = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data)), false);
			}

			if ($statement->execute($data) === true) {
				$sequence = null;

				switch (strstr($query, ' ', true)) {
					case 'INSERT':
					case 'REPLACE':
						return $this->pdo->lastInsertId($sequence);

					case 'UPDATE':
					case 'DELETE':
						return $statement->rowCount();

					case 'SELECT':
					case 'EXPLAIN':
					case 'PRAGMA':
					case 'SHOW':
						return self::fetch($statement);
				}
				return true;
			}
		} catch (\Exception $exception) {
			vd($exception->getMessage());
			return false;
		}
	}
	public static function fetch($stmt) {
		$result = $stmt->fetchAll();
		if (count($result) === 0) {
			return $result;
		}
		foreach (array_keys($result[0]) as $idx => $name) {
			$meta = $stmt->getColumnMeta($idx);
			if (isset($meta['sqlite:decl_type'])) {
				if (preg_match("#int|dec|num|real|float|long|short|double|byte|timestamp#i", $meta['sqlite:decl_type'])) {
					for ($i = 0, $len = count($result); $i < $len; $i += 1) {
						$result[$i][$name] = floatval($result[$i][$name]);
					}
				}
			}
		}
		return $result;
	}
}
