<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider as BaseDoctrineServiceProvider;

/**
 * Class DoctrineServiceProvider
 *
 * http://silex.sensiolabs.org/doc/providers/doctrine.html
 */
class DoctrineServiceProvider extends BaseDoctrineServiceProvider
{
    /**
     * [register description]
     * @param  Application $app [description]
     * @return [type]      [description]
     */
    public function register(Application $app)
    {
        $app['dbs.options'] = array(
            'db' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'DATABASE_NAME',
                'host'     => '127.0.0.1',
                'user'     => 'DATABASE_USER',
                'password' => 'DATABASE_PASS',
                'charset'  => 'utf8',
            ),
        );

        //
        parent::register($app);
    }
}
