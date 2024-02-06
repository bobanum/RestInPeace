<?php
namespace RestInPeace;

class Table extends TableOrView {
	public $views = [];

	static function from($config, $database = null) {
		if ($config instanceof self) {
			return $config;
		}
		$result = parent::from($config, $database);
		
		foreach ($result->views as $name => $view) {
			$result->views[$name] = View::from($view);
		}
		foreach ($result->relations as $name => $relation) {
			$result->relations[$name] = Relation::from($relation);
		}
		return $result;
	}
}