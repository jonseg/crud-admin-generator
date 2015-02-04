<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

error_reporting(E_ALL ^E_USER_DEPRECATED);
ini_set("display_errors", 1);

// http://silex.sensiolabs.org/doc/web_servers.html#php-5-4
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/app.php';

$app->run();
