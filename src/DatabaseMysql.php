<?php

namespace RestInPeace;

class DatabaseMysql extends Database {
	public $database;
	public $username;
	public $password;
	public $host;
	public $port;

	public function __construct($database, $username = "root", $password = "", $host = "localhost", $port = "3306") {
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
		$this->port = $port;
	}
	function newPDO($options = []) {
		$options = self::$connectionOptions + [
			\PDO::ATTR_AUTOCOMMIT => true,
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci", time_zone = "+00:00";',
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		] + $options;
		$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $this->host, $this->port, $this->database);
		$pdo = new \PDO($dsn, $this->username, $this->password, $options);
		vd($pdo->errorInfo());
		return $pdo;
	}
	public function normalizeQuery($query) {
		$query = parent::normalizeQuery($query);
		$query = strtr($query, '"', '`');
		return $query;
	}
	static function fromConfig() {
		return new static(
			Config::get('DB_DATABASE', 'database'),
			Config::get('DB_USERNAME', 'root'),
			Config::get('DB_PASSWORD', ''),
			Config::get('DB_HOST', 'localhost'),
			Config::get('DB_PORT', 3306)
		);
	}
	public function getTables() {
		return "TODO Implement getTables()";
	}
	public function getViews() {
		return "TODO Implement getViews()";
	}
	public function getColumns($table) {
		return "TODO Implement getColumns()";
	}
	public function getIndexes($table) {
		return "TODO Implement getIndexes()";
	}

	public function getPrimaryKey($table) {
		return "TODO Implement getPrimaryKey()";
	}
	public function getForeignKeys($table) {
		return "TODO Implement getForeignKeys()";
	}
}
