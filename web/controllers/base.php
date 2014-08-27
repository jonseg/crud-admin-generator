<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/app.php';


require_once __DIR__.'/backend/index.php';
require_once __DIR__.'/director/index.php';
require_once __DIR__.'/director_backend/index.php';
require_once __DIR__.'/ldirectord_realserver/index.php';
require_once __DIR__.'/ldirectord_virtual_real_server/index.php';
require_once __DIR__.'/ldirectord_virtualserver/index.php';
require_once __DIR__.'/realservertype/index.php';
require_once __DIR__.'/web/index.php';
require_once __DIR__.'/web_alias/index.php';
require_once __DIR__.'/web_redir/index.php';
require_once __DIR__.'/web_redir_url/index.php';

if(file_exists(__DIR__.'/custom.php')){
        require_once __DIR__.'/custom.php';
}

$app->match('/', function () use ($app) {

    return $app['twig']->render('ag_dashboard.html.twig', array());
        
})
->bind('dashboard');


$app->run();
