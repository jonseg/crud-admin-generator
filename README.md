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

    git clone https://github.com/jonseg/crud-admin-generator.git admingenerator

    cd admingenerator

Download composer:

    curl -sS https://getcomposer.org/installer | php

Install vendors:

    php composer.phar install

Prepare your environment configs:

    cp -f config.php.dist config.php

You need point the document root of your virtual host to /path_to/admingenerator/web

This is an example of VirtualHost:

    <VirtualHost *:80>
        DocumentRoot /path_to/admingenerator/web
        DirectoryIndex index.php
        <Directory "/path_to/admingenerator/web">
            Options Indexes FollowSymLinks
            Order Allow,Deny
            Allow from all
            AllowOverride all
            <IfModule mod_php5.c>
                php_admin_flag engine on
                php_admin_flag safe_mode off
                php_admin_value open_basedir none
            </ifModule>
        </Directory>
    </VirtualHost>
    
You can customize the url using the .htaccess file, maybe this will help you:
[http://stackoverflow.com/questions/24952846/how-do-i-remove-the-web-from-my-url/24953439#24953439](http://stackoverflow.com/questions/24952846/how-do-i-remove-the-web-from-my-url/24953439#24953439)


Generate CRUD backend
---------------------

Edit the file config.php and set your database connection data:

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


You need to set the url of the resources folder.

Change this line:

    $app['asset_path'] = '/resources';

For the url of your project, for example:

    $app['asset_path'] = 'http://domain.com/crudadmin/resources';

There is also more optional customization you can do

    // determine image path for image fields in database
    // I.E field value would be image.jpg, result would be <img src="http://somepath/dist/images/image.jpg" />
    $app['image_fields'] = array(
        //'table_name.field_name' => 'http://somepath/dist/images/',
    );

    // If the automapping of foreign key for drop down list does not the job, you can
    // force a mapping here
    $app['foreign_key_mapping'] = array(
        //'main_table_name.main_table_field' => 'foreign_table_name.foreign_table_field'
    );

    // Allow user to add additional menu links
    $app['menu_links'] = array(
        //['name' => 'MENU NAME', 'url' => 'http://menu-url.com', 'fa-icon' => ''],
    );

    // Allow user to add additional buttons in the action menu, to call external APIs
    $app['call_to_action'] = array(
      'synonyms' => array(
          0 => array(
              'btn_name' => 'btn name 1',
              'method' => 'put',
              'url' => 'http://some-url/1.0/route/{id}',
              'data'=> json_encode(array(
                  'param' => value
              )),
              'callback' => 'refresh'
          ),
          1 => array(
              'btn_name' => 'btn name 2',
              'method' => 'post',
              'url' => 'http://some-url/1.0/route/{id}',
              'data'=> json_encode(array(
                  'param' => value
              )),
              'callback' => 'refresh'
          )
      )
    );

Now, execute the command that will generate the CRUD backend:

    php console generate:admin

**This is it!** Now access with your favorite web browser.


The command generates one menu section for each database table. **Now will be much easier to list, create, edit and delete rows!**


Customize the result
--------------------

The generated code is fully configurable and editable, you just have to edit the corresponding files.
However, this approach is not recommended. Doing so will prevent you from re-generating the admin, which might
be necessary in case you are doing changes to the database structure.

 - The **controller** you can find it in **/web/controllers/TABLE_NAME/index.php**
 - The **views** are in **/web/views/TABLE_NAME**

It has generated a folder for each database table.


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

* Jon Segador <info@jonsegador.com>
* Personal site: [http://jonsegador.com/](http://jonsegador.com/)
* Twitter: *[@jonseg](https://twitter.com/jonseg)*
* CRUD Admin Generator webpage: [http://crud-admin-generator.com](http://crud-admin-generator.com)

Contributors
------------
* Jean-Michael Cyr


  [1]: http://crud-admin-generator.com
