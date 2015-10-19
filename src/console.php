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

		$getTablesQuery = "SHOW TABLES";
		$getTablesResult = $app['db']->fetchAll($getTablesQuery, array());

		$_dbTables = array();
		$dbTables = array();

		foreach($getTablesResult as $getTableResult){

			$_dbTables[] = reset($getTableResult);

			$dbTables[] = array(
				"name" => reset($getTableResult),
				"columns" => array()
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
			$table_columns = array();
			$primary_key = false;

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

					$table_columns[] = array(
						"name" => $column['Field'],
						"primary" => $column['Field'] == $primary_key ? true : false,
						"nullable" => $column['Null'] == "NO" ? true : false,
						"auto" => $column['Extra'] == "auto_increment" ? true : false,
						"external" => $column['Field'] != $primary_key ? $external_table : false,
						"type" => $column['Type']
					);
				}

			}
			else{
				continue;
			}


			$tables[$table_name] = array(
				"primary_key" => $primary_key,
				"columns" => $table_columns
			);

		}

		$MENU_OPTIONS = "";
		$BASE_INCLUDES = "";

		foreach($tables as $table_name => $table){

			$table_columns = $table['columns'];

			$TABLENAME = $table_name;
			$TABLE_PRIMARYKEY = $table['primary_key'];

			$TABLECOLUMNS_ARRAY = "";
			$TABLECOLUMNS_TYPE_ARRAY = "";			
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

			$MENU_OPTIONS .= "" .
			"<li class=\"treeview {% if option is defined and (option == '" . $TABLENAME . "_list' or option == '" . $TABLENAME . "_create' or option == '" . $TABLENAME . "_edit') %}active{% endif %}\">" . "\n" .
			"    <a href=\"#\">" . "\n" .
			"        <i class=\"fa fa-folder-o\"></i>" . "\n" .
			"        <span>" . $TABLENAME . "</span>" . "\n" .
			"        <i class=\"fa pull-right fa-angle-right\"></i>" . "\n" .
			"    </a>" . "\n" .
			"    <ul class=\"treeview-menu\" style=\"display: none;\">" . "\n" .
			"        <li {% if option is defined and option == '" . $TABLENAME . "_list' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $TABLENAME . "_list') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> List</a></li>" . "\n" .
			"        <li {% if option is defined and option == '" . $TABLENAME . "_create' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $TABLENAME . "_create') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> Create</a></li>" . "\n" .
			"    </ul>" . "\n" .
			"</li>" . "\n\n";

			$BASE_INCLUDES .= "require_once __DIR__.'/" . $TABLENAME . "/index.php';" . "\n";

			$count_externals = 0;
			foreach($table_columns as $table_column){
				$TABLECOLUMNS_ARRAY .= "\t\t" . "'". $table_column['name'] . "', \n";
				$TABLECOLUMNS_TYPE_ARRAY .= "\t\t" . "'". $table_column['type'] . "', \n";				
				if(!$table_column['primary'] || ($table_column['primary'] && !$table_column['auto'])){
					$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => '', \n";
					$TABLECOLUMNS_INITIALDATA_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => \$row_sql['".$table_column['name']."'], \n";

					$INSERT_QUERY_FIELDS[] = "`" . $table_column['name'] . "`";
					$INSERT_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']";
					$UPDATE_QUERY_FIELDS[] = "`" . $table_column['name'] . "` = ?";
					$UPDATE_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']";

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
					$search_names_foreigner_key = array('name','title','e?mail','username');

					if(!empty($app['usr_search_names_foreigner_key'])){
						$search_names_foreigner_key = array_merge(
							$app['usr_search_names_foreigner_key'],
							$search_names_foreigner_key);
					}

						// pattern to match a name column, with or whitout a 3 to 4 Char prefix
					$search_names_foreigner_key = '#^(.{3,4}_)?('.implode('|',$search_names_foreigner_key).')$#i';

					foreach($external_table['columns'] as $external_column){
						if( preg_match($search_names_foreigner_key, $external_column['name'])){
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
				"\t\t" . "if( \$table_columns_type[\$i] != \"blob\") {" . "\n" .
				"\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \$row_sql[\$table_columns[\$i]];" . "\n" . 
				"\t\t" . "} else {" .
				
				"\t\t\t\t" . "if( !\$row_sql[\$table_columns[\$i]] ) {" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \"0 Kb.\";" . "\n" .
				"\t\t\t\t" . "} else {" . "\n" .
				   
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \" <a target='__blank' href='menu/download?id=\" . \$row_sql[\$table_columns[0]];" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"&fldname=\" . \$table_columns[\$i];" . "\n" . 
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"&idfld=\" . \$table_columns[0];" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"'>\";" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= number_format(strlen(\$row_sql[\$table_columns[\$i]]) / 1024, 2) . \" Kb.\";" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"</a>\";" . "\n" .
				    
				"\t\t\t\t" . "}" . "\n" .
				
				"\t\t" . "}";
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
			$_controller = str_replace("__TABLECOLUMNS_TYPE_ARRAY__", $TABLECOLUMNS_TYPE_ARRAY, $_controller);			
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
			$_list_template = str_replace("__TABLENAMEUP__", ucfirst(strtolower($TABLENAME)), $_list_template);

			$_create_template = file_get_contents(__DIR__.'/../gen/create.html.twig');
			$_create_template = str_replace("__TABLENAME__", $TABLENAME, $_create_template);
			$_create_template = str_replace("__TABLENAMEUP__", ucfirst(strtolower($TABLENAME)), $_create_template);
			$_create_template = str_replace("__EDIT_FORM_TEMPLATE__", $EDIT_FORM_TEMPLATE, $_create_template);

			$_edit_template = file_get_contents(__DIR__.'/../gen/edit.html.twig');
			$_edit_template = str_replace("__TABLENAME__", $TABLENAME, $_edit_template);
			$_edit_template = str_replace("__TABLENAMEUP__", ucfirst(strtolower($TABLENAME)), $_edit_template);
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
