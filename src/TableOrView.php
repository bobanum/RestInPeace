<?php

namespace RestInPeace;

/**
 * Class TableOrView
 *
 * This class represents a database table or view.
 * It provides methods to interact with the database structure.
 */
class TableOrView {
	use HasAccessors;
	use HasHateoas;
	/** @var array $configKeys An array of configuration keys used in the TableOrView class. */
	static $configKeys = ['name', 'columns', 'primary_key', 'views', 'relations', 'foreign_keys',];
	/** @var string $name The name of the table or view. */
	public $name;
	/** @var array $columns An array to store the columns of the table or view */
	public $columns = [];
	/** @var array $indexes Array to store index definitions */
	public $indexes = [];
	/** @var array $_primary_key Array to store primary key(s) */
	protected $_primary_key = [];
	/** @var array $relations Array to store relationships for the table or view */
	public $relations = [];
	/** @var array $foreign_keys An array to store foreign key relationships */
	public $foreign_keys = [];
	/** @var array $schema An array to store the schema information */
	protected $schema = [];
	/** @var Database $database The database connection or instance */
	protected $database;
	/**
	 * Constructor for the TableOrView class.
	 *
	 * @param Database $database The database connection or instance.
	 * @param string|null $name The name of the table or view. Default is null.
	 */
	function __construct(Database $database, $name = null) {
		$this->database = $database;
		$this->name = $name;
	}
	/**
	 * Retrieves the primary key of the table or view.
	 *
	 * @return string The primary key of the table or view.
	 */
	function get_primary_key() {
		if (empty($this->_primary_key)) {
			$this->_primary_key = $this->database->getPrimaryKey($this);
		}
		return implode('_', $this->_primary_key);
	}
	/**
	 * Sets the primary key for the table or view.
	 *
	 * @param mixed $value The value to set as the primary key.
	 * @return void
	 */
	function set_primary_key($value) {
		$this->_primary_key = $value;
	}
	/**
	 * Checks if the current table or view is valid.
	 *
	 * @return bool Returns true if the table or view is valid, false otherwise.
	 */
	public function isValid() {
		if (empty($this->name)) {
			return false;
		}
		if (empty(RestInPeace::$included_tables) && empty(RestInPeace::$excluded_tables)) {
			return true;
		}
		if (!empty(RestInPeace::$included_tables)) {
			return in_array($this->name, RestInPeace::$included_tables);
		}
		if (!empty(RestInPeace::$excluded_tables)) {
			return !in_array($this->name, RestInPeace::$excluded_tables);
		}
		return false;
	}
	/**
	 * Adds a relationship between the current table or view and another table or view.
	 *
	 * @param TableOrView $table The table or view to relate to.
	 * @param string $key The key to use for the relationship.
	 * @return void
	 */
	public function addRelation(TableOrView $table, $key) {
		$relation = new Relation(Relation::BELONGS_TO, $this, $table, $key);
		$this->relations[$relation->name] = $relation;
		$table->relations[$this->name] = new Relation(Relation::HAS_MANY, $table, $this, $key);
	}

	/**
	 * Determines if the given table schema represents a junction table.
	 *
	 * A junction table is typically used in many-to-many relationships between two other tables.
	 *
	 * @param array $tableSchema The schema of the table to check.
	 * @return bool Returns true if the table is a junction table, false otherwise.
	 */
	static function isJunctionTable($tableSchema) {
		$columns = $tableSchema['columns'];
		$columns = array_filter($columns, function ($column) {
			$exclude = ['id', 'created_at', 'updated_at'];
			if (in_array($column['name'], $exclude)) return false;
			if ($column['pk'] === 1) return false;
			if (substr($column['name'], -3) === '_id') return false;
			return true;
		});
		return count($columns) === 0;
	}
	/**
	 * Processes the provided views by applying necessary suffixes.
	 *
	 * @param array &$views An array of views to be processed. This parameter is passed by reference.
	 */
	public function processSuffixedViews(&$views) {
		$suffixes = array_map(fn($view) => $view->get_suffixe($this->name), $views);
		$suffixes = array_filter($suffixes);
		$table_views = array_flip($suffixes);
		$table_views = array_map(fn($viewName) => $views[$viewName], $table_views);
		if (Config::get('HIDE_SUFFIXED_VIEWS', true)) {
			$views = array_diff_key($views, $suffixes);
		}
		$this->views = $table_views;
		return $table_views;
	}
	/**
	 * Retrieves the columns of the table or view.
	 *
	 * @param bool $implode Optional. If true, the columns will be returned as a comma-separated string. 
	 *                      If false, the columns will be returned as an array. Default is true.
	 * @return string|array The columns of the table or view, either as a comma-separated string or an array.
	 */
	public static function getCols($implode = true) {
		if (!isset($_GET['cols'])) {
			return "*";
		}
		$cols = explode(",", $_GET['cols']);
		$cols = array_map(function ($col) {
			$col = htmlspecialchars($col);
			return sprintf('`%s`', $col);
		}, $cols);
		if ($implode === true) {
			$cols = implode(",", $cols);
		}
		return $cols;
	}
	/**
	 * Adds parameters to the query array from the given source.
	 *
	 * @param array $query The query array to which parameters will be added. Passed by reference.
	 * @param mixed $source The source from which parameters will be extracted and added to the query array.
	 */
	static function addParams(&$query = [], $source = null) {
		$source = $source ?? $_GET;
		if (isset($source['by'])) {
			if (!isset($source['order'])) {
				$source['order'] = 'ASC';
			}

			$query[] = sprintf('ORDER BY "%s" %s', $source['by'], $source['order']);
		}

		if (isset($source['limit'])) {
			$query[] = sprintf('LIMIT %u', $source['limit']);

			if (isset($source['offset'])) {
				$query[] = sprintf('OFFSET %u', $source['offset']);
			}
		}
		return $query;
	}
	/**
	 * Retrieves all records from the table or view.
	 *
	 * @param string $suffix The suffix to append to the endpoint URL. Default is "index".
	 * @param array $params Optional parameters to include in the request.
	 * @return mixed The result of the query.
	 */
	function all($suffix = "index", $params = []) {
		$query = [];
		if (isset($this->views[$suffix])) {
			$tableName = $this->views[$suffix]->name;
		} else {
			$tableName = $this->name;
		}
		$cols = self::getCols();

		$query['SELECT'] = [
			sprintf('%s FROM `%s`', $cols, $tableName),
		];
		self::addParams($query, $params);
		self::addParams($query);
		$result = $this->database->execute($query);

		if ($result === false) {
			return Response::replyCode(404);
		}

		// if (empty($result)) {
		// 	return Response::replyCode(204);
		// }
		return $result;
	}
	/**
	 * Executes a function with the provided arguments.
	 *
	 * @param mixed $args The arguments to pass to the function.
	 * @return mixed The result of the function execution.
	 */
	function execute(...$args) {
		return $this->database->execute(...$args);
	}
	/**
	 * Finds a record by its ID.
	 *
	 * @param int $id The ID of the record to find.
	 * @param string $suffix Optional. The suffix to use. Default is "index".
	 * @return mixed The found record, or null if no record is found.
	 */
	function find($id, $suffix = "index") {
		$query = [];
		if (isset($this->views[$suffix])) {
			$tableName = $this->views[$suffix]->name;
		} else {
			$tableName = $this->name;
		}
		$cols = self::getCols();

		$query['SELECT'] = sprintf('%s FROM `%s`', $cols, $tableName);
		$query['WHERE'] = sprintf('`%s` = ?', $this->primary_key);

		self::addParams($query);
		$result = [$query];
		$result = $this->database->execute($query, [$id]);
		// $result = [$query];

		if ($result === false) {
			return Response::replyCode(404);
		}

		// if (empty($result)) {
		// 	return Response::replyCode(204);
		// }
		return $result;
	}
	/**
	 * Establishes a relationship between the current entity and a related entity.
	 *
	 * @param string $related The name of the related entity.
	 * @param mixed $id The identifier of the related entity.
	 * @param string $suffix Optional. The suffix to append to the relationship. Default is "index".
	 *
	 * @return mixed The result of the relationship operation.
	 */
	function related($related, $id, $suffix = "index") {
		if (!isset($this->relations[$related])) {
			return Response::replyCode(404);
		}
		$relation = $this->relations[$related];
		$related = RestInPeace::getSchemaTable($relation->foreign_table);
		$realRelated = $related;
		if (isset($related->views[$suffix])) {
			$related = $related->views[$suffix];
		}

		$query = [];
		$query[] = $relation->getSelect();
		$this->addParams($query);
		$result = $this->database->execute($query, [$id]);

		if ($result === false) {
			return Response::replyCode(404);
		}
		// if (empty($result)) {
		// 	return Response::replyCode(204);
		// }
		if ($relation->type === Relation::BELONGS_TO) {
			$result = $result[0];
			$realRelated->addHateoas($result);
		} else {
			$realRelated->addHateoasArray($result);
		}
		return $result;
	}

	/**
	 * Creates an instance of the class from the given configuration.
	 *
	 * @param array $config The configuration array.
	 * @param Database $database Optional. The database connection or instance. Default is null.
	 * @return self An instance of the class.
	 */
	static function from($config, Database $database = null) {
		if ($config instanceof self) {
			return $config;
		}
		$result = new static($database);
		foreach (static::$configKeys as $key) {
			if (isset($config[$key])) {
				$result->$key = $config[$key];
			}
		}
		return $result;
	}
}
