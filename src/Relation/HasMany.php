<?php

namespace RestInPeace\Relation;

use RestInPeace\Relation;
use RestInPeace\TableOrView;

class HasMany extends Relation {
	public function __construct(TableOrView $table, TableOrView $foreign_table, $foreign_key = null) {
		parent::__construct(Relation::HAS_MANY, $table, $foreign_table, $foreign_key);
	}
	public function getSelect($condition = '= ?') {
		if (is_numeric($condition) && $condition > 1) {
			$condition = 'in (' . implode(',', array_fill(0, $condition, '?')) . ')';
		} else {
			$condition = '= ?';
		}
		$query = parent::getSelect([
			"WHERE" => "f.`{$this->foreign_key}` {$condition}",
		]);
		return $query;
	}
	public function outputModel() {
		return <<<"EOD"
		public function get_{$this->name}() {
			return \$this->hasMany('{$this->foreign_table->name}', '{$this->foreign_key}');
		}
	EOD;
	}
	public function fetch($id, $with = []) {
		// if (!is_array($id)) {
		// 	$id = [$id];
		// }
		// $query = $this->getSelect(count($id));
		// $result = $this->foreign_table->execute($query, [$id]);
		$result = parent::fetch($id);
		foreach ($result as $model) {
			$model->fetchWith($with);
		}
		return $result;
	}
	public function fetchWith($array, $with = []) {
		foreach ($array as $model) {
			$model->fetchWith($with);
		}
		return $this;
	}
}
