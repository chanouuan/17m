<?php

error_reporting(1);
set_time_limit(30);
date_default_timezone_set('PRC');

$_COOKIE['token'] = rawurldecode('e3f6%252FSJn0kNbr52wvlXo50uE8xpnqMMZXm26xDo3%252BpExL8CCtTuFtsKSfjvPNrqBBKHS');

define('APPLICATION_PATH', __DIR__);
define('APPLICATION_URL', 'http://192.168.1.214:82');
define('TIMESTAMP', $_SERVER['REQUEST_TIME']);

include APPLICATION_PATH . '/application/library/Common.php';
include APPLICATION_PATH . '/application/library/Init.php';

$controller->run();
