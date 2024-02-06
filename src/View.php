<?php
namespace RestInPeace;

class View extends TableOrView {
	public function get_table_name() {
		if (empty($this->table_name)) {
			$this->table_name = $this->database->getTableName($this->name);
		}
		return $this->table_name;
	}
	public function get_suffixe($tableName = "") {
		if (!empty($tableName)) {
			$tableName = '^' . $tableName;
		}
		$regex = sprintf("~^%s__([a-z]+)$~", $tableName);
		if (preg_match($regex, $this->name, $matches)) {
			return $matches[1];
		}
		return false;
	}
	static function from($config, $database = null) {
		if ($config instanceof self) {
			return $config;
		}
		$result = parent::from($config, $database);
		
		return $result;
	}
}