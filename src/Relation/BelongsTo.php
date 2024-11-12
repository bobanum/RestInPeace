<?php

namespace RestInPeace\Relation;

use RestInPeace\Relation;
use RestInPeace\TableOrView;

class BelongsTo extends Relation {
	public function __construct(TableOrView $table, TableOrView $foreign_table, $foreign_key = null) {
		parent::__construct(Relation::BELONGS_TO, $table, $foreign_table, $foreign_key);
		$this->foreign_key = $foreign_key ?? $foreign_table->get_foreign_key();
		$this->name = preg_replace("~_id$~", '', $this->foreign_key);
	}
	public function getSelect($condition = '= ?') {
		if (is_numeric($condition) && $condition > 1) {
			$condition = 'in (' . implode(',', array_fill(0, $condition, '?')) . ')';
		} else {
			$condition = '= ?';
		}
		$sql = parent::getSelect([
			"INNER" => "JOIN `{$this->table}` l",
			"ON" => "f.id = l.`{$this->foreign_key}`",
			"WHERE" => "l.id {$condition}",
		]);
		return $sql;
	}
	public function outputModel() {
		return <<<"EOD"
		public function get_{$this->name}() {
			return \$this->belongsTo('{$this->foreign_table}', '{$this->foreign_key}');
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
		if (count($result) === 0) {
			return null;
		} else {
			$model = $result[0];
			$model->fetchWith($with);
			return $model;
		}
	}
	public function fetchWith($model, $with = []) {
		$model->fetchWith($with);
		return $this;
	}
}
