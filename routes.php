<?php

use RestInPeace\Response;
use RestInPeace\Router;

Router::get('/', function() {
	return "Hello World!";
});
Router::get('/#num/#alpha?', function($id, $nom) {
	return [$id, $nom];
});
Router::get('/#num', function($code) {
	return new Response(Response::$HTTP[$code]);
});
