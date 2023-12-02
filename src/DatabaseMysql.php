<?php
namespace RestInPeace;

abstract class DatabaseMysql extends Database {
	public $database;
	public $username;
	public $password;
	public $host;
	public $port;
	public $_pdo;

	public function __construct($database, $username = "root", $password = "", $host = "localhost", $port = "3306") {
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
		$this->port = $port;
	}
	function connect($options = []) {
		if (!empty($this->_pdo)) {
			return $this->_pdo;
		}
		$options = self::$connectionOptions + [
			\PDO::ATTR_AUTOCOMMIT => true,
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci", time_zone = "+00:00";',
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		] + $options;
		$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $this->host, $this->port, $this->database);
		$this->_pdo = new \PDO($dsn, $this->username, $this->password, $options);
		return $this->_pdo;
	}
}