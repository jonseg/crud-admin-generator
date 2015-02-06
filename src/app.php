<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

$app = require_once __DIR__.'/bootstrap.php';

// http://silex.sensiolabs.org/doc/providers/security.html
$app->register(new Crud\Provider\SecurityServiceProvider());

$app->mount('/', require_once __DIR__.'/routes.php');

return $app;
