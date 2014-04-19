CRUD Admin Generator
===================

What is CRUD Admin Generator?
-----------------------------

**CRUD Admin Generator** ([http://crud-admin-generator.com][1]) is a tool to **generate a complete backend from a MySql database** where you can create, read, update and delete records in a database. 

**The backend is generated in seconds** without configuration files where there is a lot of *"magic"* and is very difficult to adapt to your needs. 

**The generated code is fully customizable and extensible.**

It has been programmed with the Silex framework, so the resulting code is PHP.


Installation
------------

Clone the repository

    git clone git@github.com:jonseg/crud-admin-generator.git admingenerator

    cd admingenerator

Download composer:

    curl -sS https://getcomposer.org/installer | php

Install vendors:

    php composer.phar install

You need point the document root of your virtual host to /path_to/admingenerator/web


Generate CRUD backend
---------------------

Edit the file /path_to/admingenerator/src/app.php and set your database conection data:

    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'dbs.options' => array(
            'db' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'DATABASE_NAME',
                'host'     => 'localhost',
                'user'     => 'DATABASE_USER',
                'password' => 'DATABASE_PASS',
                'charset'  => 'utf8',
            ),
        )
    ));


Now, execute the command that will generate the CRUD backend:

    php console generate:admin

**This is it!** Now access with your favorite web browser.


The command generates one menu section for each database table. **Now will be much easier to list, create, edit and delete rows!**


Customize the result
--------------------

The generated code is fully configurable and editable, you just have to edit the corresponding files.

 - The **controller** you can find it in **/web/controllers/TABLE_NAME/index.php**
 - The **views** are in **/web/views/TABLE_NAME**

It has generated a folder for each datbase table.


Contributing
------------

If you want to contribute code to CRUD Admin Generator, we are waiting for your pull requests!

Some suggestions for improvement could be:

 - Different form fields depending on data type.: datetime, time...
 - Create admin user with a login and logout page.
 - Generate CRUD for tables with more than one primary key.
 - Any other useful functionality!

Author
------

* Jon Segador <jonseg@nazcalabs.com>
* Twitter : *[@jonseg](https://twitter.com/jonseg)*
* Personal blog: *[http://jonsegador.com/](http://jonsegador.com/)*
* CRUD Admin Generator webpage: *[http://crud-admin-generator.com](http://crud-admin-generator.com)*


  [1]: http://crud-admin-generator.com