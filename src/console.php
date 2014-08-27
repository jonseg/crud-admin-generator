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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Schema\Table;

$console = new Application('CRUD Admin Generator command instalation', '1.0');

$console
    ->register('generate:admin')
    ->setDefinition(array())
    ->setDescription("Generate administrator")
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
		
		# Check crud-tables
		if(array_key_exists('tableTitles',$app['config'])){
			$tableTitlesEnabled=true;
		}else{
			$tableTitlesEnabled=false;
		}

		$dialog = (new Symfony\Component\Console\Application)->getHelperSet()->get('dialog');
		$output->writeln('<info>Creating the "src/app.php" file</info>');
		$output->writeln('<comment>Some parameters of your database are missing. Please provide them.</comment>');
		$database_driver = $dialog->ask($output,'<question>database_driver</question> (<comment>pdo_mysql</comment>): ');
		$database_host = $dialog->ask($output,'<question>database_host</question> (<comment>127.0.0.1</comment>): ');
		//$database_port = $dialog->ask($output,'<question>database_port</question> (<comment>null</comment>): ');
		$database_name = $dialog->ask($output,'<question>database_name</question> (<comment>symfony</comment>): ');
		$database_user = $dialog->ask($output,'<question>database_user</question> (<comment>root</comment>): ');
		$database_password = $dialog->ask($output,'<question>database_password</question> (<comment>null</comment>): ');
		$database_charset = $dialog->ask($output,'<question>database_charset</question> (<comment>UTF8</comment>): ');
		$output->writeln('<comment>Insert your admin credentials.</comment>');
		$admin_username = $dialog->ask($output,'<error>admin_username</error> (<comment>admin</comment>): ');
		$admin_password = $dialog->ask($output,'<error>admin_password</error> (<comment>foo</comment>): ');
		$_app = file_get_contents(__DIR__.'/../gen/app.php');
		$_app = str_replace("__DATABASE_DRIVER__", !empty($database_driver) ? $database_driver : 'pdo_mysql', $_app) ;
		$_app = str_replace("__DATABASE_HOST__", !empty($database_host) ? $database_host : '127.0.0.1', $_app) ;
		$_app = str_replace("__DATABASE_NAME__", !empty($database_name) ? $database_name : 'symfony', $_app) ;
		$_app = str_replace("__DATABASE_USER__", !empty($database_user) ? $database_user : 'root', $_app) ;
		$_app = str_replace("__DATABASE_PASS__", !empty($database_password) ? $database_password : 'null', $_app) ;
		$_app = str_replace("__DATABASE_CHARSET__", !empty($database_charset) ? $database_charset : 'UTF8', $_app) ;
		$_app = str_replace("__ADMIN_USERNAME__", !empty($admin_username) ? $admin_username : 'admin', $_app) ;
		$_app = str_replace("__ADMIN_PASSWORD__", !empty($admin_password) ? (new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder())->encodePassword($admin_password, '') : (new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder())->encodePassword('foo', ''), $_app) ;
		$fp = fopen(__DIR__."/../src/app.php", "w+");
		fwrite($fp, $_app);
		fclose($fp);

	    $getTablesQuery = "SHOW TABLES";
	    $getTablesResult = $app['db']->fetchAll($getTablesQuery, array());  	

	    $_dbTables = array();
	    $dbTables = array();

	    foreach($getTablesResult as $getTableResult){
	    	$tableTitle='';

			$_dbTables[] = reset($getTableResult);

		if($tableTitlesEnabled){
			if(array_key_exists(reset($getTableResult),$app['config']['tableTitles'])){
				$tableTitle=$app['config']['tableTitles'][reset($getTableResult)];
			}
		}
		if($tableTitle=='') $tableTitle=ucfirst(strtolower(reset($getTableResult)));

	    	$dbTables[] = array(
	    		"name" => reset($getTableResult), 
	    		"columns" => array(),
			"title" => $tableTitle
	    	);
	    }

    	foreach($dbTables as $dbTableKey => $dbTable){
		    $getTableColumnsQuery = "SHOW COLUMNS FROM `" . $dbTable['name'] . "`";
		    $getTableColumnsResult = $app['db']->fetchAll($getTableColumnsQuery, array());    

		    foreach($getTableColumnsResult as $getTableColumnResult){
		    	$dbTables[$dbTableKey]['columns'][] = $getTableColumnResult;
		    }

    	}

		$tables = array();
    	foreach($dbTables as $dbTable){

    		if(count($dbTable['columns']) <= 1){
    			continue;
    		}

    		$table_name = $dbTable['name'];
    		$table_title = $dbTable['title'];
    		$table_columns = array();
    		$primary_key = false;
			$enabled = false;

    		$primary_keys = 0;
    		$primary_keys_auto = 0;
    		foreach($dbTable['columns'] as $column){
    			if($column['Key'] == "PRI"){
    				$primary_keys++;
    			}    			
    			if($column['Extra'] == "auto_increment"){
    				$primary_keys_auto++;
    			}    			
    		}

    		if($primary_keys === 1 || ($primary_keys > 1 && $primary_keys_auto === 1)){

	    		foreach($dbTable['columns'] as $column){

	    			$external_table = false;

	    			if($primary_keys > 1 && $primary_keys_auto == 1){
		    			if($column['Extra'] == "auto_increment"){
		    				$primary_key = $column['Field'];
		    			}
	    			}
	    			else if($primary_keys == 1){
		    			if($column['Key'] == "PRI"){
		    				$primary_key = $column['Field'];
		    			}
		    		}
		    		else{
		    			continue 2;
		    		}

					if(substr($column['Field'], -3) == "_id"){
					    $_table_name = substr($column['Field'], 0, -3);

					    if(in_array($_table_name, $_dbTables)){
					        $external_table = $_table_name;
					    }
					}
					if($column['Field'] == 'enabled'){$enabled=true;}

	    			$table_columns[] = array(
	    				"name" => $column['Field'],
	    				"primary" => $column['Field'] == $primary_key ? true : false,
	    				"nullable" => $column['Null'] == "NO" ? true : false,
	    				"auto" => $column['Extra'] == "auto_increment" ? true : false,
	    				"external" => $column['Field'] != $primary_key ? $external_table : false,
	    				"type" => $column['Type'],
	    			);
	    		}

    		}
    		else{
    			continue;
    		}


			$tables[$table_name] = array(
				"primary_key" => $primary_key,
				"columns" => $table_columns,
				"title" => $table_title,
				"enabled" => $enabled
			);

    	}

    	$MENU_OPTIONS = "";
    	$BASE_INCLUDES = "";

		foreach($tables as $table_name => $table){

			$table_columns = $table['columns'];

			$TABLENAME = $table_name;
			$TABLETITLE = $table['title'];
			$TABLE_PRIMARYKEY = $table['primary_key'];

			$TABLECOLUMNS_ARRAY = "";
			$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY = "";
			$TABLECOLUMNS_INITIALDATA_ARRAY = "";

			$EXTERNALS_FOR_LIST = "";
			$EXTERNALSFIELDS_FOR_FORM = "";
			$FIELDS_FOR_FORM = "";

			$INSERT_QUERY_FIELDS = array();
			$INSERT_EXECUTE_FIELDS = array();
			$UPDATE_QUERY_FIELDS = array();
			$UPDATE_EXECUTE_FIELDS = array();

         $EDIT_FORM_TEMPLATE = "";
         $custom_menu=array();
         if(array_key_exists('custom_menu',$app['config']) AND array_key_exists($TABLENAME,$app['config']['custom_menu'])){
            if(is_array($app['config']['custom_menu'][$TABLENAME])){
               $custom_menu=$app['config']['custom_menu'][$TABLENAME];
            }else{
               $custom_menu[]=$app['config']['custom_menu'][$TABLENAME];
            }
         }

         $MENU_OPTIONS .= "" .
         "<li class=\"treeview {% if option is defined and (option == '" . $TABLENAME . "_list' or option == '" . $TABLENAME . "_create' or option == '" . $TABLENAME . "_edit' " ;
         foreach($custom_menu as $menu){
            $MENU_OPTIONS .= " or option == '" . $menu['path'] . "'";
         }
         $MENU_OPTIONS .= "" .
         ") %}active{% endif %}\">" . "\n" .
         "    <a href=\"#\">" . "\n" .
         "        <i class=\"fa fa-folder-o\"></i>" . "\n" .
         "        <span>" . $TABLETITLE . "</span>" . "\n" .
         "        <i class=\"fa pull-right fa-angle-right\"></i>" . "\n" .
         "    </a>" . "\n" .
         "    <ul class=\"treeview-menu\" style=\"display: none;\">" . "\n" .
         "        <li {% if option is defined and option == '" . $TABLENAME . "_list' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $TABLENAME . "_list') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> List</a></li>" . "\n" .
         "        <li {% if option is defined and option == '" . $TABLENAME . "_create' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $TABLENAME . "_create') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> Create</a></li>" . "\n";

         foreach($custom_menu as $menu){
            $MENU_OPTIONS .= "" .
            "        <li {% if option is defined and option == '" . $menu['path'] . "' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $menu['path'] . "') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> " . $menu['name'] . "</a></li>" . "\n" ;
         }


         $MENU_OPTIONS .= "" .
         "    </ul>" . "\n" .
         "</li>" . "\n\n";
			
			$BASE_INCLUDES .= "require_once __DIR__.'/" . $TABLENAME . "/index.php';" . "\n";

			$count_externals = 0;
			foreach($table_columns as $table_column){
				$TABLECOLUMNS_ARRAY .= "\t\t" . "'". $table_column['name'] . "', \n";
				if(!$table_column['primary'] || ($table_column['primary'] && !$table_column['auto'])){
					switch ($table_column['type']){
						case 'tinyint(1)':
							$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => (boolean) 0, \n";
							$TABLECOLUMNS_INITIALDATA_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => (boolean) \$row_sql['".$table_column['name']."'], \n";
							break;
						case 'date':
							$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => new \DateTime(), \n";
							$TABLECOLUMNS_INITIALDATA_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => new \DateTime(\$row_sql['".$table_column['name']."']), \n";
							break;
						default:
							$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => '', \n";
							$TABLECOLUMNS_INITIALDATA_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => \$row_sql['".$table_column['name']."'], \n";
							break;
					}

					$INSERT_QUERY_FIELDS[] = "`" . $table_column['name'] . "`";
					switch ($table_column['type']){
						case 'tinyint(1)':
							$INSERT_EXECUTE_FIELDS[] = "(boolean) \$data['" . $table_column['name'] . "']";
							break;
						case 'date':
							$INSERT_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']->format('Y-m-d')";
							break;
						default:
							$INSERT_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']";
							break;
					}
					$UPDATE_QUERY_FIELDS[] = "`" . $table_column['name'] . "` = ?";
					switch ($table_column['type']){
                                                case 'date':
							$UPDATE_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']->format('Y-m-d')";
                                                        break;
		                                default:
							$UPDATE_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']";
							break;
					}

					if(strpos($table_column['type'], 'text') !== false){
						$EDIT_FORM_TEMPLATE .= "" . 
	                    "\t\t\t\t\t\t\t\t\t" . "<div class='form-group'>" . "\n" . 
	                    "\t\t\t\t\t\t\t\t\t" . "    {{ form_label(form." . $table_column['name'] . ") }}" . "\n" . 
	                    "\t\t\t\t\t\t\t\t\t" . "    {{ form_widget(form." . $table_column['name'] . ", { attr: { 'class': 'form-control textarea', 'style': 'width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;' }}) }}" . "\n" . 
	                    "\t\t\t\t\t\t\t\t\t" . "</div>" . "\n\n";
					}
					else {
						$EDIT_FORM_TEMPLATE .= "" . 
	                    "\t\t\t\t\t\t\t\t\t" . "<div class='form-group'>" . "\n" . 
	                    "\t\t\t\t\t\t\t\t\t" . "    {{ form_label(form." . $table_column['name'] . ") }}" . "\n" . 
	                    "\t\t\t\t\t\t\t\t\t" . "    {{ form_widget(form." . $table_column['name'] . ", { attr: { 'class': 'form-control' }}) }}" . "\n" . 
	                    "\t\t\t\t\t\t\t\t\t" . "</div>" . "\n\n";
                	}
				}
				
				$field_nullable = $table_column['nullable'] ? "true" : "false";

				if($table_column['external']){
					$external_table = $tables[$table_column['external']];

					$external_primary_key = $external_table['primary_key'];
					$external_select_field = false;

					foreach($external_table['columns'] as $external_column){
						if($external_column['name'] == "name" || 
							$external_column['name'] == "title" || 
							$external_column['name'] == "email" || 
							$external_column['name'] == "username"){
							$external_select_field = $external_column['name'];	
						}
					}

					if(!$external_select_field){
						$external_select_field = $external_primary_key;
					}
					
					$external_cond = $count_externals > 0 ? "else if" : "if";

					$EXTERNALS_FOR_LIST .= "" . 
		            "\t\t\t" . $external_cond . "(\$table_columns[\$i] == '" . $table_column['name'] . "'){" . "\n" . 
		            "\t\t\t" . "    \$findexternal_sql = 'SELECT `" . $external_select_field . "` FROM `" . $table_column['external'] . "` WHERE `" . $external_primary_key . "` = ?';" . "\n" . 
		            "\t\t\t" . "    \$findexternal_row = \$app['db']->fetchAssoc(\$findexternal_sql, array(\$row_sql[\$table_columns[\$i]]));" . "\n" . 
		            "\t\t\t" . "    \$rows[\$row_key][\$table_columns[\$i]] = \$findexternal_row['" . $external_select_field . "'];" . "\n" . 
		            "\t\t\t" . "}" . "\n";


		            $EXTERNALSFIELDS_FOR_FORM .= "" . 
				    "\t" . "\$options = array();" . "\n" . 
				    "\t" . "\$findexternal_sql = 'SELECT `" . $external_primary_key . "`, `" . $external_select_field . "` FROM `" . $table_column['external'] . "`';" . "\n" . 
				    "\t" . "\$findexternal_rows = \$app['db']->fetchAll(\$findexternal_sql, array());" . "\n" . 
				    "\t" . "foreach(\$findexternal_rows as \$findexternal_row){" . "\n" . 
				    "\t" . "    \$options[\$findexternal_row['" . $external_primary_key . "']] = \$findexternal_row['" . $external_select_field . "'];" . "\n" . 
				    "\t" . "}" . "\n" . 
				    "\t" . "if(count(\$options) > 0){" . "\n" . 
				    "\t" . "    \$form = \$form->add('" . $table_column['name'] . "', 'choice', array(" . "\n" . 
				    "\t" . "        'required' => " . $field_nullable . "," . "\n" . 
				    "\t" . "        'choices' => \$options," . "\n" . 
				    "\t" . "        'expanded' => false," . "\n" . 
				    "\t" . "        'constraints' => new Assert\Choice(array_keys(\$options))" . "\n" . 
				    "\t" . "    ));" . "\n" . 
				    "\t" . "}" . "\n" . 
				    "\t" . "else{" . "\n" . 
				    "\t" . "    \$form = \$form->add('" . $table_column['name'] . "', 'text', array('required' => " . $field_nullable . "));" . "\n" . 
				    "\t" . "}" . "\n\n";

		            $count_externals++;
				}
				else{
					if(!$table_column['primary']){

						if(strpos($table_column['type'], 'text') !== false){
							$FIELDS_FOR_FORM .= "" . 
						    "\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'textarea', array('required' => " . $field_nullable . "));" . "\n";
						}
						elseif($table_column['type']=='tinyint(1)'){
							$FIELDS_FOR_FORM .= "" .
							"\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'checkbox', array('required' => false ));" . "\n";
						}
						elseif(strpos($table_column['type'], 'date') !== false){
							$FIELDS_FOR_FORM .= "" .
						    "\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'date', array('required' => " . $field_nullable . ", 'input' => 'datetime', 'widget' => 'choice'));" . "\n";
						}
						else{
							$FIELDS_FOR_FORM .= "" . 
						    "\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'text', array('required' => " . $field_nullable . "));" . "\n";
						}
					}
					else if($table_column['primary'] && !$table_column['auto']){
							$FIELDS_FOR_FORM .= "" . 
						    "\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'text', array('required' => " . $field_nullable . "));" . "\n";
					}
				}
			}

			if($count_externals > 0){
				$EXTERNALS_FOR_LIST .= "" . 
	            "\t\t\t" . "else{" . "\n" . 
	            "\t\t\t" . "    \$rows[\$row_key][\$table_columns[\$i]] = \$row_sql[\$table_columns[\$i]];" . "\n" . 
	            "\t\t\t" . "}" . "\n";
			}
			
			if($EXTERNALS_FOR_LIST == ""){
				$EXTERNALS_FOR_LIST .= "" . 
	            "\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \$row_sql[\$table_columns[\$i]];" . "\n";
			}

					if($table['enabled']){
						$ENABLED_ACTIONS='{% if row[\'enabled\'] == 0 %}<a href="{{ path(\''. $TABLENAME .'_enable\', { id: row[primary_key] }) }}" class="btn btn-primary btn-xs">Enable</a>{% else %}<a href="{{ path(\''. $TABLENAME .'_disable\', { id: row[primary_key] }) }}" class="btn btn-danger btn-xs">Disable</a>{% endif %}';
					}else{
						$ENABLED_ACTIONS='';
					}


			$INSERT_QUERY_VALUES = array();
			foreach($INSERT_QUERY_FIELDS as $INSERT_QUERY_FIELD){
				$INSERT_QUERY_VALUES[] = "?";
			}
			$INSERT_QUERY_VALUES = implode(", ", $INSERT_QUERY_VALUES);
			$INSERT_QUERY_FIELDS = implode(", ", $INSERT_QUERY_FIELDS);
			$INSERT_EXECUTE_FIELDS = implode(", ", $INSERT_EXECUTE_FIELDS);
			
			$UPDATE_QUERY_FIELDS = implode(", ", $UPDATE_QUERY_FIELDS);
			$UPDATE_EXECUTE_FIELDS = implode(", ", $UPDATE_EXECUTE_FIELDS);	

			$_controller = file_get_contents(__DIR__.'/../gen/controller.php');
			$_controller = str_replace("__TABLENAME__", $TABLENAME, $_controller);
			$_controller = str_replace("__TABLE_PRIMARYKEY__", $TABLE_PRIMARYKEY, $_controller);
			$_controller = str_replace("__TABLECOLUMNS_ARRAY__", $TABLECOLUMNS_ARRAY, $_controller);
			$_controller = str_replace("__TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY__", $TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY, $_controller);
			$_controller = str_replace("__TABLECOLUMNS_INITIALDATA_ARRAY__", $TABLECOLUMNS_INITIALDATA_ARRAY, $_controller);
			$_controller = str_replace("__EXTERNALS_FOR_LIST__", $EXTERNALS_FOR_LIST, $_controller);
			$_controller = str_replace("__EXTERNALSFIELDS_FOR_FORM__", $EXTERNALSFIELDS_FOR_FORM, $_controller);
			$_controller = str_replace("__FIELDS_FOR_FORM__", $FIELDS_FOR_FORM, $_controller);

			$_controller = str_replace("__INSERT_QUERY_FIELDS__", $INSERT_QUERY_FIELDS, $_controller);
			$_controller = str_replace("__INSERT_QUERY_VALUES__", $INSERT_QUERY_VALUES, $_controller);
			$_controller = str_replace("__INSERT_EXECUTE_FIELDS__", $INSERT_EXECUTE_FIELDS, $_controller);

			$_controller = str_replace("__UPDATE_QUERY_FIELDS__", $UPDATE_QUERY_FIELDS, $_controller);
			$_controller = str_replace("__UPDATE_EXECUTE_FIELDS__", $UPDATE_EXECUTE_FIELDS, $_controller);
			

			$_list_template = file_get_contents(__DIR__.'/../gen/list.html.twig');
			$_list_template = str_replace("__TABLENAME__", $TABLENAME, $_list_template);
			$_list_template = str_replace("__TABLENAMETITLE__", $TABLETITLE, $_list_template);
			if(array_key_exists($TABLENAME,$app['config']['customActionsList'])){
				$_custom_action_list=$app['config']['customActionsList'][$TABLENAME];
			}else{
				$_custom_action_list='';
			}
			$_list_template = str_replace("__CUSTOMACTIONLIST__", $_custom_action_list, $_list_template);
			$_list_template = str_replace("__ENABLED_ACTIONS__", $ENABLED_ACTIONS, $_list_template);

			$_create_template = file_get_contents(__DIR__.'/../gen/create.html.twig');
			$_create_template = str_replace("__TABLENAME__", $TABLENAME, $_create_template);
			$_create_template = str_replace("__TABLENAMETITLE__", $TABLETITLE, $_create_template);
			$_create_template = str_replace("__EDIT_FORM_TEMPLATE__", $EDIT_FORM_TEMPLATE, $_create_template);

			$_edit_template = file_get_contents(__DIR__.'/../gen/edit.html.twig');
			$_edit_template = str_replace("__TABLENAME__", $TABLENAME, $_edit_template);
			$_edit_template = str_replace("__TABLENAMETITLE__", $TABLETITLE, $_edit_template);
			$_edit_template = str_replace("__EDIT_FORM_TEMPLATE__", $EDIT_FORM_TEMPLATE, $_edit_template);

			$_menu_template = file_get_contents(__DIR__.'/../gen/menu.html.twig');
			$_menu_template = str_replace("__MENU_OPTIONS__", $MENU_OPTIONS, $_menu_template);

			$_base_file = file_get_contents(__DIR__.'/../gen/base.php');
			$_base_file = str_replace("__BASE_INCLUDES__", $BASE_INCLUDES, $_base_file);
			
			@mkdir(__DIR__."/../web/controllers/".$TABLENAME, 0755);
			@mkdir(__DIR__."/../web/views/".$TABLENAME, 0755);

			$fp = fopen(__DIR__."/../web/controllers/".$TABLENAME."/index.php", "w+");
			fwrite($fp, $_controller);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/".$TABLENAME."/create.html.twig", "w+");
			fwrite($fp, $_create_template);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/".$TABLENAME."/edit.html.twig", "w+");
			fwrite($fp, $_edit_template);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/".$TABLENAME."/list.html.twig", "w+");
			fwrite($fp, $_list_template);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/controllers/base.php", "w+");
			fwrite($fp, $_base_file);
			fclose($fp);		

			$fp = fopen(__DIR__."/../web/views/menu.html.twig", "w+");
			fwrite($fp, $_menu_template);
			fclose($fp);	

		}

});

return $console;
