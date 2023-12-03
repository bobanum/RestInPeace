<?php

use RestInPeace\Response;
use RestInPeace\Router;

Router::get('', function() {
	return "Hello World!";
});
Router::get('#any', function($table) {
	return [$table];
});
Router::get('#num', function($code) {
	return new Response(Response::sendCode($code));
});
