<?php

function vd() {
	// var_dump(debug_backtrace());
	echo "<pre>\n";
	echo str_repeat("\u{2501}", 80) . "\n";
	echo sprintf("%s::%s:<b>%s</b>\n", debug_backtrace()[1]['class'] ?? debug_backtrace()[0]['file'], debug_backtrace()[1]['function'], debug_backtrace()[0]['line']);
	foreach (func_get_args() as $arg) {
		echo str_repeat("\u{2500}", 80) . "\n";
		var_export($arg);
		echo "\n";
	}
	echo str_repeat("\u{2501}", 80) . "\n";
	echo "</pre>";
}
function vdd() {
	// var_dump(debug_backtrace());
	echo "<pre>\n";
	echo str_repeat("\u{2501}", 80) . "\n";
	echo sprintf("%s::%s:<b>%s</b>\n", debug_backtrace()[1]['class'] ?? debug_backtrace()[0]['file'], debug_backtrace()[1]['function'], debug_backtrace()[0]['line']);
	foreach (func_get_args() as $arg) {
		echo str_repeat("\u{2500}", 80) . "\n";
		var_export($arg);
		echo "\n";
	}
	echo str_repeat("\u{2501}", 80) . "\n";
	echo "</pre>";
	die;
}
function vdj() {
	header('Content-Type: application/json');
	[$caller, $callee] = debug_backtrace();
	$result = [];
	$result['file'] = $caller['file'];
	$result['line'] = $caller['line'];
	$result['function'] = $callee['function'];
	if (!empty($callee['class'])) {
		$result['function'] = $callee['class'] . "::" . $result['function'];
	}
	$dump = func_get_args();
	$dump = array_combine(array_map(fn($i) => "var " . $i, array_keys($dump)), $dump);
	$result += $dump;
	// array_push($result, ...$dump);
	// $result['dump'] = func_get_args();
	echo json_encode($result, JSON_PRETTY_PRINT);
	die;
}

