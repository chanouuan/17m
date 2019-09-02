<?php

error_reporting(1);
set_time_limit(30);
date_default_timezone_set('PRC');

define('APPLICATION_PATH', __DIR__);
define('APPLICATION_URL', 'http://10.0.0.194:96');
define('TIMESTAMP', $_SERVER['REQUEST_TIME']);

include APPLICATION_PATH . '/application/library/Common.php';
include APPLICATION_PATH . '/application/library/Init.php';

$controller->run();