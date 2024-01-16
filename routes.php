<?php

use RestInPeace\Response;
use RestInPeace\RestInPeace as RIP;
use RestInPeace\Router;

$response = Router::get('/', function () {
	$schema = RIP::getSchema();
	$result = [];
	foreach ($schema['tables'] as $table => $config) {
		if (!RIP::isVisible($config)) {
			continue;
		}
		$result['url_'.$table] = sprintf("%s/%s", RIP::$root, $table);
	}
	return $result;
}) ?:
Router::group('/#slug', function ($table) {
	if (!RIP::isVisible($table)) return;

	return Router::get('/', function ($table) {
		$result = RIP::getAll($table);
		return $result;
	}) ?:
	
	Router::group('/#num', function ($table, $id) {
		$result = RIP::getOne($table, $id)[0];
		return Router::get('/', function ($table, $id) use ($result) {
			return $result;
		}) ?:
		Router::get('/#slug', function ($table, $id, $slug) use ($result) {
			$sub = RIP::getRelated($table, $id, $slug);
			$result[$slug] = $sub;
			return $result;
		});
	});
}) ?:
Response::replyCode(404);
// Router::get('/#num/#alpha?', function ($id, $nom) {
// 	return [$id, $nom];
// });
// Router::get('/#num', function ($code) {
// 	return new Response(Response::$HTTP[$code]);
// });
Response::reply($response);
