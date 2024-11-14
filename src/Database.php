<?php

namespace RestInPeace;

abstract class Database {
	use HasAccessors;
	/** @var string A regex pattern to match primary keys */
	static protected $primary_key_pattern = '^id$';	// A regex pattern to match primary keys
	/** @var string A regex pattern to match foreign keys */
	static protected $foreign_key_pattern = '^([a-z0-9_]+)_id$';	// A regex pattern to match primary keys
	/** @var PDO $_pdo The PDO instance for database connection */
	private $_pdo;
	/** @var PDOStatements[] $statements An array to hold prepared SQL statements */
	private $statements = [];
	/** @var array $schema An array to store the database schema */
	public $schema = [];
	/** @var Tables[] $tables Stores the database tables information */
	private $tables = [];
	/** @var View[] $views An array to store view data */
	public $views = [];
	/** @var array $connectionOptions Options for database connection */
	public static $connectionOptions = [
		\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
		\PDO::ATTR_STRINGIFY_FETCHES => false,
	];
	/**
	 * Constructor for the Database class.
	 * 
	 * Initializes a new instance of the Database class.
	 */
	public function __construct() {
		$this->_pdo = $this->newPDO();
	}
	/**
	 * Establishes and returns a PDO (PHP Data Objects) connection.
	 *
	 * @return PDO The PDO instance representing the database connection.
	 */
	function get_pdo() {
		if (!empty($this->_pdo)) {
			return $this->_pdo;
		}
		return $this->_pdo = $this->newPDO();
	}
	/**
	 * Abstract method to create a new PDO instance.
	 *
	 * @param array $options Optional parameters for the PDO instance.
	 * @return PDO
	 */
	abstract function newPDO($options = []);
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
	/**
	 * Finds the suffix for a given view name and optionally a table name.
	 *
	 * @param string $viewName The name of the view to find the suffix for.
	 * @param string $tableName Optional. The name of the table to find the suffix for. Default is an empty string.
	 * @return mixed The suffix for the given view and table name.
	 */
	static public function findSuffixe($viewName, $tableName = '') {
		$regex = sprintf("~^%s__([a-z]+)$~", $tableName);
		if (preg_match($regex, $viewName, $matches)) {
			return $matches[1];
		}
		return false;
	}
	/**
	 * Analyzes the current state of the database.
	 *
	 * This method performs an analysis on the database to gather
	 * information about its structure, contents, and other relevant
	 * metrics. The specific details of what is analyzed and how the
	 * results are presented depend on the implementation.
	 *
	 * @return array An array containing the results of the analysis.
	 */
	public function analyse() {
		/** @var Table[] $tables */
		$tables = $this->getTables();
		/** @var View[] $views */
		$views = $this->getViews();
		// vdd($tables, $views);
		foreach ($tables as $table) {
			if (empty($views)) break;	// If we just removed the last view
			$table->processSuffixedViews($views);
		}
		// Reanalyse to complete foreign keys and foreign tables
		foreach ($tables as $table) {
			foreach ($table->foreign_keys as $fk) {
				$foreignTable = $fk['table'];
				$table->addRelation(new Relation\BelongsTo($table, $tables[$foreignTable], $fk['from']));
			}
			// Check for unprocessed foreign keys
			foreach ($table->columns as $columnName => $column) {
				preg_match("~" . self::$foreign_key_pattern . "~", $column['name'], $matches);
				if (empty($matches)) continue;
				$relationName = $matches[1];
				if (isset($table->relations[$relationName])) continue;

				$ft = $table->findForeignTable($tables, $columnName);
				if (empty($ft)) continue;
				$relation = new Relation\BelongsTo($table, $ft, $column['name']);
				$table->addRelation($relation);
			}
			$relBT = array_filter($table->relations, fn($rel) => $rel->type === Relation::BELONGS_TO);
			while (count($relBT) > 1) {
				$rel1 = array_pop($relBT);
				if ($rel1->foreign_table->get_foreign_key() !== $rel1->foreign_key) continue;
				foreach ($relBT as $rel2) {
					if ($rel2->foreign_table->get_foreign_key() !== $rel2->foreign_key) continue;
					// var_dump($table->name, $rel1->foreign_table->name, $rel2->foreign_table->name, $rel1->foreign_key);
					$rel1->foreign_table->addRelation(new Relation\BelongsToMany($rel1->foreign_table, $rel2->foreign_table, $table));
					$rel2->foreign_table->addRelation(new Relation\BelongsToMany($rel2->foreign_table, $rel1->foreign_table, $table));
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
	abstract public function getPrimaryKey($table);
	abstract public function getForeignKeys($table);

	/**
	 * Prepares an SQL statement for execution.
	 *
	 * @param string $query The SQL query to be prepared.
	 * @return \PDOStatement The prepared statement object.
	 */
	public function prepare($query) {
		return $this->pdo->prepare($query);
		//TODO: Check relevance of this method
		// $hash = crc32($query);
		// if (empty($this->statements[$hash])) {
		// 	$this->statements[$hash] = $this->pdo->prepare($query);
		// }
		// return $this->statements[$hash];
	}
	/**
	 * Executes a given SQL query with the provided data.
	 *
	 * @param string $query The SQL query to be executed.
	 * @param array $data The data to be bound to the query parameters.
	 * @return mixed The result of the executed query.
	 */
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
			vdd($query);
			throw new \Exception($exception->getMessage());

			vdj($exception->getMessage(), $query, $data);
			// return ['status' => 'error', 'message' => $exception->getMessage()];
		}
	}
	public function executeClass($class, $query, $data) {
		$query = $this->normalizeQuery($query);
		file_put_contents('query.sql', $query . "\n", FILE_APPEND);
		try {
			$statement = $this->prepare($query);

			$statement->setFetchMode(\PDO::FETCH_CLASS, $class);
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
			vdd($query, $data);
			throw new \Exception($exception->getMessage());

			vdj($exception->getMessage(), $query, $data);
			// return ['status' => 'error', 'message' => $exception->getMessage()];
		}
	}
	/**
	 * Fetches the result from a given statement.
	 *
	 * @param \PDOStatement $stmt The prepared statement to fetch the result from.
	 * @return mixed The fetched result, typically an array or false if no result.
	 */
	public static function fetch(\PDOStatement $stmt) {
		$result = $stmt->fetchAll();
		if (count($result) === 0) {
			return $result;
		}
		// foreach (array_keys($result[0]) as $idx => $name) {
		// 	$meta = $stmt->getColumnMeta($idx);
		// 	if (isset($meta['sqlite:decl_type'])) {
		// 		if (preg_match("#int|dec|num|real|float|long|short|double|byte|timestamp#i", $meta['sqlite:decl_type'])) {
		// 			for ($i = 0, $len = count($result); $i < $len; $i += 1) {
		// 				$result[$i][$name] = floatval($result[$i][$name]);
		// 			}
		// 		}
		// 	}
		// }
		return $result;
	}
}
