<?php

use RestInPeace\Response;
use RestInPeace\RestInPeace as RIP;
use RestInPeace\Router;

Router::get('/', function () {
	$schema = RIP::getSchema();
	return $schema;
});
Router::group('/#slug', function () {
	Router::get('/', function ($table) {
		return RIP::actionGetAll($table);
	});
	// Router::get('/#num', function ($table, $id) {
	// 	return [$table, $id];
	// });
});
// Router::get('/#num/#alpha?', function ($id, $nom) {
// 	return [$id, $nom];
// });
// Router::get('/#num', function ($code) {
// 	return new Response(Response::$HTTP[$code]);
// });
