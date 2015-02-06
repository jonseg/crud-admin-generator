<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

use Silex\Application;

$app = new Application();

// Settings
$app['security_path'] = '/security';
$app['asset_path'] = '/resources';
$app['debug'] = true;

// http://silex.sensiolabs.org/doc/providers/session.html
$app->register(new Crud\Provider\SessionServiceProvider());

// http://silex.sensiolabs.org/doc/providers/form.html
$app->register(new Crud\Provider\FormServiceProvider());

// http://silex.sensiolabs.org/doc/providers/translation.html
$app->register(new Crud\Provider\TranslationServiceProvider());

// http://silex.sensiolabs.org/doc/providers/validator.html
$app->register(new Crud\Provider\ValidatorServiceProvider());

// http://silex.sensiolabs.org/doc/providers/url_generator.html
$app->register(new Crud\Provider\UrlGeneratorServiceProvider());

// http://silex.sensiolabs.org/doc/providers/doctrine.html
$app->register(new Crud\Provider\DoctrineServiceProvider());

// http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app->register(new Crud\Provider\SwiftmailerServiceProvider());

// http://silex.sensiolabs.org/doc/providers/service_controller.html
$app->register(new Crud\Provider\ControllerServiceProvider());

// ExceptionServiceProvider
$app->register(new Crud\Provider\ExceptionServiceProvider());

// http://silex.sensiolabs.org/doc/providers/twig.html
$app->register(new Crud\Provider\TwigServiceProvider());

// http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
$app['twig']->addExtension(new Crud\Twig\AssetTwigFunction($app));
$app['twig']->addExtension(new Crud\Twig\CamelizeTwigFunction($app));

return $app;
