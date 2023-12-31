<?php

namespace RestInPeace;

abstract class Database {
	use HasAccessors;
	public $_pdo;
	public $statements = [];
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
		if (is_array($query)) {
			$query = implode(' ', $query);
		}
		return $query;
	}
	static public function findSuffixe($viewName, $tableName = '') {
		if ($tableName) {
			$tableName = '^' . $tableName;
		}
		if (preg_match("~{$tableName}__([a-z]+)$~", $viewName, $matches)) {
			return $matches[1];
		}
		return false;
	}
	public function analyse() {
		$tables = $this->getTables();
		$views = $this->getViews();
		foreach ($tables as &$table) {
			$tableName = $table['name'];
			$table_views = array_map(fn ($view) => self::findSuffixe($view['name'], $tableName), $views);
			$table_views = array_filter($table_views);
			$table['views'] = array_flip($table_views);
			if (!Config::get('KEEP_ALL_VIEWS', false)) {
				foreach($table['views'] as $suffixe => $viewName) {
					$table['views'][$suffixe] = $views[$viewName];
					unset($views[$viewName]);
				}
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
