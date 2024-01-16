<?php
namespace RestInPeace;

class Relation {
	public $name;
	public $table;
	public $type;
	public $foreign_key;

	const HAS_ONE = 0;
	const BELONGS_TO = 1;
	const HAS_MANY = 2;
	const BELONGS_TO_MANY = 3;
	const BELONGS_TO_THROUGH = 4;
	const HAS_MANY_THROUGH = 5;

	function __construct($type, $table, $foreign_key) {
		$this->table = $table;
		$this->type = $type;
		$this->foreign_key = $foreign_key;
		$this->name = ($this->type === self::BELONGS_TO) ? preg_replace("~_id$~", '', $foreign_key) : ($table->name ?? $table);
	}
	function toConfig() {
		$result = (array) $this;
		$result['table'] = $this->table->name ?? $this->table;
		return $result;
	}
}