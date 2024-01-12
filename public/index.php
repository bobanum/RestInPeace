<?php
include_once "../vendor/autoload.php";
use RestInPeace\RestInPeace;

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

RestInPeace::guard();

include "../routes.php";

