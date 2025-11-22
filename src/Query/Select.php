<?php
namespace RestInPeace\Query;
use RestInPeace\Query;
class Select extends Query {
	public function __construct($table) {
		parent::__construct($table, 'SELECT');
	}
}