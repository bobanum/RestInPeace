<?php

use RestInPeace\Response;
use RestInPeace\Router;

Router::get('/', function () {
	return "Hello World!";
});
Router::group('/#slug', function () {
	Router::get('/#num', function ($table, $id) {
		return [$table, $id];
	});
});
Router::get('/#num/#alpha?', function ($id, $nom) {
	return [$id, $nom];
});
Router::get('/#num', function ($code) {
	return new Response(Response::$HTTP[$code]);
});
