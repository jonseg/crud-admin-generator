<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider as BaseTwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TwigServiceProvider
 *
 * http://silex.sensiolabs.org/doc/providers/twig.html
 */
class TwigServiceProvider extends BaseTwigServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);
        $app['twig.path'] = array(realpath(__DIR__.'/../../views'));
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];

        $twig->addFunction(
            new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
                /** @var \Symfony\Component\HttpFoundation\Request $request */
                $request = $app['request'];
                $url = '';
                if ($request instanceof Request) {
                    $url = $request->getBaseUrl();
                }

                return sprintf('%s/%s', $url, $asset);
            })
        );

        $twig->addFilter(
            new \Twig_SimpleFilter('ceil', function ($value) {
                return ceil($value);
            })
        );
    }
}
