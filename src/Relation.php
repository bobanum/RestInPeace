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
	/** @var string $table The name of the database table associated with the relation */
	public $table;
	/** @var string $foreign_table The name of the foreign table in the relationship */
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
	function __construct($type, TableOrView $table, TableOrView $foreign_table, $foreign_key) {
		$this->type = $type;
		$this->table = $table->name;
		$this->foreign_table = $foreign_table->name;
		$this->foreign_key = $foreign_key;
		$this->name = ($this->type === self::BELONGS_TO) ? preg_replace("~_id$~", '', $foreign_key) : $this->foreign_table;
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
	function getSelect($long = true) {
		if ($this->type === Relation::BELONGS_TO) {
			return $long
				? sprintf('SELECT * FROM %1$s WHERE id = (SELECT %2$s FROM %3$s WHERE id = ?)', $this->foreign_table, $this->foreign_key, $this->table)
				: sprintf('SELECT * FROM %1$s WHERE id = ?', $this->foreign_table);
		} else if ($this->type === Relation::HAS_MANY) {
			return sprintf('SELECT * FROM `%1$s` WHERE `%2$s` = ?', $this->foreign_table, $this->foreign_key);
		}
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