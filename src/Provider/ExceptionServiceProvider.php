<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExceptionServiceProvider
 *
 * http://silex.sensiolabs.org/doc/usage.html#error-handlers
 */
class ExceptionServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app->error(
            function (\Exception $e, $code) use ($app) {
                if ($app['debug']) {
                    return; // exibir erro no ambiente desenvolvimento.
                }
                switch ($code) {
                    case 404:
                        $message = $app['twig']->render('security/error.twig', array(
                            'code' => 404,
                            'message' => 'Page not found, we could not find the page you were looking for.',
                            'error' => $e->getMessage(),
                        ));
                        break;
                    default:
                        $message = $app['twig']->render('security/error.twig', array(
                            'code' => 500,
                            'message' => 'Something went wrong, we will work on fixing that right away.',
                            'error' => $e->getMessage(),
                        ));
                }

                return new Response($message, $code);
            }
        );
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
        // TODO: Implement boot() method.
    }
}
