<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider as BaseTwigServiceProvider;

/**
 * Class TwigServiceProvider
 *
 * http://silex.sensiolabs.org/doc/providers/twig.html
 */
class TwigServiceProvider extends BaseTwigServiceProvider
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        parent::register($app);
        $app['twig.path'] = array(
            realpath(__DIR__.'/../../views'),
            'generator' => realpath(__DIR__.'/../Generator/views'),
        );
        $app['twig.options'] = array('cache' => false, 'strict_variables' => true);
    }
}
