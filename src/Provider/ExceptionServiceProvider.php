<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;

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
                unset($e);
                if ($app['debug']) {
                    return; // exibir erro no ambiente desenvolvimento.
                }
                switch ($code) {
                    case 404:
                        $message = 'A página solicitada não pôde ser encontrado.';
                        break;
                    default:
                        $message = 'Lamentamos, mas algo deu terrivelmente errado.';
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
