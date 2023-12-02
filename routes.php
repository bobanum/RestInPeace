<?php
use ArrestDB\ArrestDB;
/**
 * Custom Route.
 */
ArrestDB::Serve('GET', '/(#any)/(#num)/backup', function ($table, $id) {
	$cols = ArrestDB::GetTableCols($table);
	$cols = array_map(function ($col) use ($table) {
		if ($col['pk'] == '1') {
			return sprintf('null %s', $col['name']);
		}
		if ($col['name'] == 'backup') {
			return 'strftime("%s", "now") backup';
		}
		if ($col['name'] == 'ref_id') {
			return sprintf('%s.%s ref_id', $table, ArrestDB::$primary_key);
		}
		return $col['name'];
	}, $cols);
	$cols = implode(",", $cols);
	$query = [];
	$query[] = sprintf('INSERT INTO "%s"', $table);
	$query[] = sprintf('SELECT %s from "%s"', $cols, $table);
	$query[] = sprintf('WHERE "%s" = ? LIMIT 1', ArrestDB::$primary_key);
	$query = sprintf('%s;', implode(' ', $query));
	$result = ArrestDB::QuerySQL($query, $id);

	if ($result === false) {
		return ArrestDB::Reply(ArrestDB::$HTTP[404]);
	}
	
	if (empty($result)) {
		return ArrestDB::Reply(ArrestDB::$HTTP[204]);
	}

	return ArrestDB::Reply(intval($result));
});

/**
 * Serve standard REST routes.
 */
ArrestDB::ServeRest();
