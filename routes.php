<?php

use RestInPeace\Response;
use RestInPeace\Router;

Router::get('/', function() {
	echo "Hello World!";
});
Router::get('/#num/#alpha?', function($id, $nom) {
	return [$id, $nom];
});
Router::get('/erreur/#num', function($code) {
	return Response::$HTTP[$code];
});
