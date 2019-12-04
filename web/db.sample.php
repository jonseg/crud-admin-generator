<?php

// rename this file to db.php once you're done

// your database details:

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(

		'dbs.options' => array(
			'db' => array(
				'driver'   => 'pdo_mysql',
				'dbname'   => 'DATABASE_NAME',
				'host'     => '127.0.0.1',
				'user'     => 'DATABASE_USER',
				'password' => 'DATABASE_PASS',
				'charset'  => 'utf8',
			),
		)
));

// the path to /resources relative to your base URL:

$app['asset_path'] = '/resources';

