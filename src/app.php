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

class queryData {
	public $start;
	public $recordsTotal;
	public $recordsFiltered;
	public $data;

	function __construct() {
	}
}

use Silex\Application;
use Dotenv\Dotenv;

// fetch config from .env file
$dotenv = new Dotenv(__DIR__.'/..');
$dotenv->load();

$app = new Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../web/views',
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
	'translator.messages' => array(),
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(

		'dbs.options' => array(
			'db' => array(
				'driver'   => getenv('DB_DRIVER') ?: 'pdo_mysql',
				'dbname'   => getenv('DB_NAME') ?: 'my-db',
				'host'     => getenv('DB_HOST') ?: '127.0.0.1',
				'user'     => getenv('DB_USER') ?: 'root',
				'password' => getenv('DB_PASSWORD') ?: '',
				'charset'  => 'utf8',
			),
		)
));

$app['asset_path'] = '/resources';
$app['debug'] = true;
	// array of REGEX column name to display for foreigner key insted of ID
	// default used :'name','title','e?mail','username'
$app['usr_search_names_foreigner_key'] = array();

return $app;
