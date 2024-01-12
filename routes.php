<?php

use RestInPeace\Response;
use RestInPeace\RestInPeace as RIP;
use RestInPeace\Router;

Router::get('/', function () {
	$schema = RIP::getSchema();
	$result = [];
	foreach ($schema['tables'] as $table => $config) {
		if (!RIP::isVisible($config)) {
			continue;
		}
		$result['url_'.$table] = sprintf("%s/%s", RIP::$root, $table);
	}
	return $result;
});
Router::group('/#slug', function ($table) {
	if (!RIP::isVisible($table)) return;

	Router::get('/', function ($table) {
		return RIP::actionGetAll($table);
	});
	
	Router::get('/#num', function ($table, $id) {
		vd($table, $id);
		$result = Router::get('/#slug', function ($table, $id, $slug) {
			vd($table, $id, $slug);
			return $id;
		});
		return $result;
		return RIP::actionGetOne($table, $id);
	});
});
// Router::get('/#num/#alpha?', function ($id, $nom) {
// 	return [$id, $nom];
// });
// Router::get('/#num', function ($code) {
// 	return new Response(Response::$HTTP[$code]);
// });
Response::reply(Response::replyCode(404));
