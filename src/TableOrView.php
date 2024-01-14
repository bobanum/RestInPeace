<?php

namespace RestInPeace;

class TableOrView {
	use HasAccessors;
	use HasHateoas;
	static $configKeys = ['name', 'columns', 'primary_key', 'foreign_keys', 'views'];
	public $name;
	public $columns = [];
	public $indexes = [];
	private $_primary_key = [];
	public $foreign_keys = [];
	private $schema = [];
	private $database;
	function __construct($database, $name = null) {
		$this->database = $database;
		$this->name = $name;
	}
	function get_primary_key() {
		if (empty($this->_primary_key)) {
			$this->_primary_key = $this->database->getPrimaryKey($this);
		}
		return implode('_', $this->_primary_key);
	}
	function set_primary_key($value) {
		$this->_primary_key = $value;
	}
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

	static function isJunctionTable($table) {
		$columns = $table['columns'];
		$columns = array_filter($columns, function ($column) {
			$exclude = ['id', 'created_at', 'updated_at'];
			if (in_array($column['name'], $exclude)) return false;
			if ($column['pk'] === 1) return false;
			if (substr($column['name'], -3) === '_id') return false;
			return true;
		});
		return count($columns) === 0;
	}
	public function processSuffixedViews(&$views) {
		$suffixes = array_map(fn ($view) => $view->get_suffixe($this->name), $views);
		$suffixes = array_filter($suffixes);
		$table_views = array_flip($suffixes);
		$table_views = array_map(fn ($viewName) => $views[$viewName], $table_views);
		if (!Config::get('KEEP_ALL_VIEWS', false)) {
			$views = array_diff_key($views, $suffixes);
		}
		$this->views = $table_views;
		return $table_views;
	}
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
	function all($suffix = "index", $params = []) {
		$query = [];
		if (isset($this->views[$suffix])) {
			$tableName = $this->views[$suffix]['name'];
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

		if (empty($result)) {
			return Response::replyCode(204);
		}
		return $result;
	}
	function find($id, $suffix = "index") {
		$query = [];
		if (isset($this->views[$suffix])) {
			$tableName = $this->views[$suffix]['name'];
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

		// if ($result === false) {
		// 	return Response::replyCode(404);
		// }

		// if (empty($result)) {
		// 	return Response::replyCode(204);
		// }
		return $result;
	}
	static function from($config, $database = null) {
		if ($config instanceof self) {
			return $config;
		}
		$result = new self($database);
		foreach (self::$configKeys as $key) {
			if (isset($config[$key])) {
				$result->$key = $config[$key];
			}
		}
		return $result;
	}
}
