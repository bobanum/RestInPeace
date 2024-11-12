<?php

namespace RestInPeace;

/**
 * Class Relation
 *
 * This class is part of the RestInPeace library and is used to handle relationships between entities.
 * 
 * @package bobanum\restinpeace
 */
class Relation {
	/** @var string $name The name of the relation */
	public $name;
	/** @var string $type The type of the relation */
	public $type;
	/** @var TableOrView $table The name of the database table associated with the relation */
	public $table;
	/** @var TableOrView $foreign_table The name of the foreign table in the relationship */
	public $foreign_table;
	/** @var string $foreign_key The foreign key associated with the relation */
	public $foreign_key;

	const HAS_ONE = 0;
	const BELONGS_TO = 1;
	const HAS_MANY = 2;
	const BELONGS_TO_MANY = 3;
	const BELONGS_TO_THROUGH = 4;
	const HAS_MANY_THROUGH = 5;

	/**
	 * Constructor for the Relation class.
	 *
	 * @param string $type The type of the relation.
	 * @param TableOrView $table The table or view involved in the relation.
	 * @param TableOrView $foreign_table The foreign table or view involved in the relation.
	 * @param string $foreign_key The foreign key used in the relation.
	 */
	function __construct($type, TableOrView $table, TableOrView $foreign_table, $foreign_key = null) {
		$this->type = $type;
		$this->table = $table;
		$this->foreign_table = $foreign_table;
		$this->foreign_key = $foreign_key ?? $table->get_foreign_key();
		$this->name = $this->foreign_table->name;
	}
	/**
	 * Converts the current relation configuration to an array format.
	 *
	 * @return array The configuration array representing the current relation.
	 */
	function toConfig() {
		$result = (array) $this;
		return $result;
	}
	/**
	 * Retrieves the select statement.
	 *
	 * @param bool $long Optional. If true, returns the long version of the select statement. Default is true.
	 * @return string The select statement.
	 */
	public function getSelect($extra = []) {
		$query = array_merge([
			"SELECT" => "f.*",
			"FROM" => "`{$this->foreign_table}` f",
		], $extra);
		return $query;
	}
	/**
	 * Fetches a record by its ID, optionally including related records.
	 *
	 * @param int $id The ID of the record to fetch.
	 * @param array $with An array of related records to include.
	 * @return mixed The fetched record, or null if not found.
	 */
	public function fetch($id) {
		if (!is_array($id)) {
			$id = [$id];
		}
		$query = $this->getSelect(count($id));
		$result = $this->foreign_table->execute($query, [$id]);
		return $result;
	}
	/**
	 * Creates a new instance of the class from the given configuration.
	 *
	 * @param array $config The configuration array to initialize the instance.
	 * @return self Returns an instance of the class.
	 */
	static function from($config) {
		if ($config instanceof self) {
			return $config;
		}
		$result = new self($config['type'], $config['table'], $config['foreign_table'], $config['foreign_key']);
		$result->name = $config['name'];
		return $result;
	}
}
