<?php

namespace RestInPeace\Trait;

use RestInPeace\Response;
use RestInPeace\RestInPeace as RIP;
use RestInPeace\Router;

/**
 * Represents the RestInPeace class.
 */
trait Routes {
	static public function baseRoutes() {
		return self::route_home()
			?: self::route_table();
	}

	static public function route_home() {
		return Router::get('/', function () {
			$schema = RIP::getSchema();
			$result = [];
			foreach ($schema['tables'] as $table => $config) {
				if (RIP::isVisible($config)) {
					$result['url_' . $table] = sprintf("%s/%s", RIP::$root, $table);
				}
			}
			
			return new Response($result);
		});
	}
	static public function route_table() {
		return Router::group('/#slug', function ($table) {
			if (!RIP::isVisible($table)) return Response::fromCode(404);

			return Router::get('/', function ($table) {
				$result = RIP::getAll($table);
				if (!$result) return Response::fromCode(404);
				return new Response($result);
			}) ?: Router::group('/#num', function ($table, $id) {
				$result = RIP::getOne($table, $id)[0];
				if (!$result) return Response::fromCode(404);

				return Router::get('/', function ($table, $id) use ($result) {
					return new Response($result);
				}) ?: Router::get('/#slug', function ($table, $id, $slug) use ($result) {
					$sub = RIP::getRelated($table, $id, $slug);
					$result[$slug] = $sub;
					return new Response($result);
				});
			});
		});
	}
}
