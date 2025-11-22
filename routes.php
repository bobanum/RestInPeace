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
		$result['url_' . $table] = sprintf("%s/%s", RIP::$root, $table);
	}
	return $result;
}) ?:
	Router::group('/#slug', function ($table) {
		if (!RIP::isVisible($table)) return Response::empty();

		return
			Router::get('/', function ($table) {
				$result = RIP::getAll($table);
				if (!$result) return Response::fromCode(404);
				return new Response($result);
			})
			?: Router::group('/#num', function ($table, $id) {
				$result = RIP::getOne($table, $id)[0];
				if (!$result) return Response::fromCode(404);
				return
					Router::get('/', function ($table, $id) use ($result) {
						return new Response($result);
					})
					?: Router::get('/#slug', function ($table, $id, $slug) use ($result) {
						$sub = RIP::getRelated($table, $id, $slug);
						$result[$slug] = $sub;
						return new Response($result);
					});
			});
	})
	?: Response::fromCode(404);
// Router::get('/#num/#alpha?', function ($id, $nom) {
// 	return [$id, $nom];
// });
// Router::get('/#num', function ($code) {
// 	return new Response(Response::$HTTP[$code]);
// });
$response->send();
