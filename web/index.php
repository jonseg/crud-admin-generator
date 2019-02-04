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


// dual composer autoloader thanks to simplesamlphp/simplesamlphp:
// loaded as a separate project
if (file_exists(dirname(dirname(__FILE__)).'/vendor/autoload.php')) {
    require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
} else {  // loaded as a library
    if (file_exists(dirname(dirname(__FILE__)).'/../../autoload.php')) {
        require_once dirname(dirname(__FILE__)).'/../../autoload.php';
    } else {
        throw new Exception('Unable to load Composer autoloader');
    }
}

require_once __DIR__.'/app.php';
require_once __DIR__.'/utils.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/controllers/base.php';
