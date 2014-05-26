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


require_once __DIR__.'/apache2_namevirtualhost/index.php';
require_once __DIR__.'/apache2_vhosts/index.php';
require_once __DIR__.'/group/index.php';
require_once __DIR__.'/group_vars/index.php';
require_once __DIR__.'/host/index.php';
require_once __DIR__.'/host_group/index.php';
require_once __DIR__.'/host_vars/index.php';
require_once __DIR__.'/interfaces/index.php';
require_once __DIR__.'/interfaces_rules/index.php';
require_once __DIR__.'/iptables_policies/index.php';
require_once __DIR__.'/iptables_rules/index.php';
require_once __DIR__.'/roletable/index.php';
require_once __DIR__.'/single_var/index.php';
require_once __DIR__.'/sshkeys/index.php';
require_once __DIR__.'/sudoers/index.php';
require_once __DIR__.'/zabbix_userparameters/index.php';

if(file_exists(__DIR__.'/custom.php')){
        require_once __DIR__.'/custom.php';
}

$app->match('/', function () use ($app) {

    return $app['twig']->render('ag_dashboard.html.twig', array());
        
})
->bind('dashboard');


$app->run();
