<?php

namespace RestInPeace\Relation;

use RestInPeace\Relation;
use RestInPeace\TableOrView;

class BelongsToMany extends Relation {
	public $pivot_table;
	public $local_key;
	public function __construct(TableOrView $table, TableOrView $foreign_table, TableOrView $pivot_table, $local_key = null, $foreign_key = null) {
		parent::__construct(Relation::BELONGS_TO_MANY, $table, $foreign_table, $foreign_key);
		$this->pivot_table = $pivot_table;
		$this->foreign_key = $foreign_key ?? $this->foreign_table->get_foreign_key();
		$this->local_key = $local_key ?? $this->table->get_foreign_key();
	}
	public function getSelect($condition = '= ?') {
		$sql = "SELECT f.*"
			. " FROM `{$this->foreign_table}` f"
			. " INNER JOIN `{$this->pivot_table}` p"
			. " ON p.`{$this->foreign_key}` = f.id"
			. " WHERE p.`{$this->local_key}` {$condition}";
		return $sql;
	}
	public function outputModel() {
		return <<<"EOD"
		public function get_{$this->name}() {
			return \$this->belongsToMany('{$this->foreign_table}', '{$this->foreign_key}', '{$this->pivot_table}', '{$this->local_key}');
		}
	EOD;
	}
	public function fetch($id) {
		$query = $this->getSelect();
		$result = $this->foreign_table->execute($query, [$id]);
		return $result;
	}
}
