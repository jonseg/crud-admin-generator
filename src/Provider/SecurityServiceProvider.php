<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\Provider\SecurityServiceProvider as BaseSecurityServiceProvider;

/**
 * Class SecurityServiceProvider
 *
 * http://silex.sensiolabs.org/doc/providers/security.html
 */
class SecurityServiceProvider extends BaseSecurityServiceProvider
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['security_path'] = preg_replace(array('/^\//', '/\/$/'), '', $app['security_path']);

        $app['security.firewalls'] = array(
            'login' => array(
                'pattern' => sprintf('^/%s/login$', $app['security_path']),
            ),
            'secured' => array(
                'pattern' => sprintf('^/%s', $app['security_path']),
                'form' => array(
                    'login_path' => sprintf('/%s/login', $app['security_path']),
                    'check_path' => sprintf('/%s/login_check', $app['security_path']),
                ),
                'logout' => array(
                    'logout_path' => sprintf('/%s/logout', $app['security_path']),
                ),
                'users' => $app->share(function () use ($app) {
                    return new UserServiceProvider($app);
                }),
            ),
        );
        // inicialize
        parent::register($app);
    }
}
