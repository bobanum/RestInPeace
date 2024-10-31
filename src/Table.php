<?php
namespace RestInPeace;

/**
 * Class Table
 * 
 * This class extends the TableOrView class and represents a table in the database.
 * 
 * @package bobanum\restinpeace
 */
class Table extends TableOrView {
	/** @var array $views An array to store view data */
	public $views = [];

	/**
	 * Creates a new instance of the Table class from the given configuration.
	 *
	 * @param array|string $config The configuration array or a string representing the table name.
	 * @param string|null $database Optional. The name of the database. If not provided, the default database will be used.
	 * @return Table The instance of the Table class.
	 */
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