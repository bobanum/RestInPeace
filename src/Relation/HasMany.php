<?php

namespace RestInPeace\Relation;

use RestInPeace\Relation;
use RestInPeace\TableOrView;

class HasMany extends Relation {
	public function __construct(TableOrView $table, TableOrView $foreign_table, $foreign_key = null) {
		parent::__construct(Relation::HAS_MANY, $table, $foreign_table, $foreign_key);
	}
	public function getSelect($condition = '= ?') {
		$sql = "SELECT f.*" .
			" FROM `{$this->foreign_table}` f" .
			" WHERE `{$this->foreign_key}` {$condition}";
		return $sql;
	}
	public function outputModel() {
		return <<<"EOD"
		public function get_{$this->name}() {
			return \$this->hasMany('{$this->foreign_table->name}', '{$this->foreign_key}');
		}
	EOD;
	}
}
