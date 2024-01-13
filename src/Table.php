<?php
namespace RestInPeace;

class Table {
	use HasAccessors;
	use HasHateoas;
	public $name;
	public $columns = [];
	public $indexes = [];
	private $_primary_key = [];
	public $foreign_keys = [];
	public $views = [];
	public $schema = [];
	public Database $database;
	function __construct($database) {
		$this->database = $database;
	}
	function get_primary_key() {
		return implode('_', $this->_primary_key);
	}
	function set_primary_key($value) {
		$this->_primary_key = $value;
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
		return($query);
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
	static function fromConfig($config, $database) {
		$result = new self($database);
		$result->name = $config['name'];
		$result->columns = $config['columns'];
		$result->primary_key = $config['primary_key'];
		$result->foreign_keys = $config['foreign_keys'];
		$result->views = $config['views'];
		return $result;
	}
}
