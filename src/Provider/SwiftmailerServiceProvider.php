<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\Provider\SwiftmailerServiceProvider as BaseSwiftmailerServiceProvider;

/**
 * Class SwiftmailerServiceProvider
 *
 * http://silex.sensiolabs.org/doc/providers/swiftmailer.html
 */
class SwiftmailerServiceProvider extends BaseSwiftmailerServiceProvider
{
    /**
     * [register description]
     * @param  Application $app [description]
     * @return [type]      [description]
     */
    public function register(Application $app)
    {
        $app['swiftmailer.options'] = array(
            'host' => 'host',
            'port' => '25',
            'username' => 'username',
            'password' => 'password',
            'encryption' => null,
            'auth_mode' => null,
        );

        //
        parent::register($app);
    }
}
