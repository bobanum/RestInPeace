<?php

namespace RestInPeace;
/**
 * Lightweight Query container.
 *
 * This class provides a minimal representation of a SQL-like query used
 * by the application. It is intentionally simple — meant for building or
 * representing queries, not for executing them or handling escaping.
 */
class Query {
	/**
	 * Table or view name.
	 * @var string
	 */
	public $table;

	/**
	 * Query type (e.g. SELECT, INSERT, UPDATE, DELETE).
	 * @var string
	 */
	public $type;

	/**
	 * Query clauses (e.g. WHERE, ORDER BY), stored as strings to be joined.
	 * @var string[]
	 */
	public $clauses = [];

	/**
	 * Selected column names. If empty, `*` is assumed when casting to string.
	 * @var string[]
	 */
	public $columns = [];

	/**
	 * Create a new Query instance.
	 *
	 * @param string $table Table or view name.
	 * @param string $type  Query type, defaults to "SELECT".
	 */
	public function __construct($table, $type = "SELECT") {
		$this->table = $table;
		$this->type = $type;
	}

	/**
	 * Return a simple SQL-like string representation of the query.
	 *
	 * Note: This is a convenience for debugging and logging. It does NOT
	 * perform escaping or produce fully safe SQL for execution.
	 *
	 * @return string
	 */
	public function __toString() {
		$cols = empty($this->columns) ? '*' : implode(', ', $this->columns);
		$base = sprintf('%s %s FROM %s', $this->type, $cols, $this->table);
		if (!empty($this->clauses)) {
			return $base . ' ' . implode(' ', $this->clauses);
		}
		return $base;
	}
}
