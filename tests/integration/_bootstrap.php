<?php

// This is global bootstrap for autoloading
use tad\FunctionMocker\FunctionMocker;

$path = dirname( __FILE__ ) . "/../../vendor/autoload.php";
require_once($path);

FunctionMocker::init(['blacklist' => dirname(__DIR__)]);
