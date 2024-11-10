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
		$sql = "SELECT f.*"
			. " FROM `{$this->foreign_table}` f"
			. " INNER JOIN `{$this->table}` l"
			. " ON f.id = l.`{$this->foreign_key}`"
			. " WHERE l.id {$condition}";
		return $sql;
	}
	public function outputModel() {
		return <<<"EOD"
		public function get_{$this->name}() {
			return \$this->belongsTo('{$this->table}', '{$this->foreign_key}');
		}
	EOD;

	}
}
