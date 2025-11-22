<?php
namespace RestInPeace\Query;
use RestInPeace\Query;
class Delete extends Query {
	public function __construct($table) {
		parent::__construct($table, 'DELETE');
	}
}