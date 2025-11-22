<?php
namespace RestInPeace\Query;
use RestInPeace\Query;
class Insert extends Query {
	public function __construct($table) {
		parent::__construct($table, 'INSERT');
	}
}