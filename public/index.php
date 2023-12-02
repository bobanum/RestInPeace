<?php
include_once "../vendor/autoload.php";
use ArrestDB\ArrestDB;
/**
 * The MIT License
 * http://creativecommons.org/licenses/MIT/
 *
 * ArrestDB 1.9.0 (github.com/alixaxel/ArrestDB/)
 * Copyright (c) 2014 Alix Axel <alix.axel@gmail.com>
 **/

include "../routes.php";
exit(ArrestDB::Reply(ArrestDB::$HTTP[400]));
