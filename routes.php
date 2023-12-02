<?php
use RestInPeace\RestInPeace;
use RestInPeace\Router;

Router::get('/', function() {
	echo "Hello World!";
});