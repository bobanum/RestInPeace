<?php
namespace RestInPeace\Query;
use RestInPeace\Query;
class Update extends Query {
	public function __construct($table) {
		parent::__construct($table, 'UPDATE');
	}
}