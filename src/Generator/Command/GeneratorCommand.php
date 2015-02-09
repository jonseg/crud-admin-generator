<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Generator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class GeneratorCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('crud:generator')
            ->setDescription('generator crud')
            ->addOption('tables', null, InputOption::VALUE_REQUIRED, 'define tables generator');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $getTables = array_map(function ($value) { return array_values($value)[0]; }, $this->get('db')->fetchAll('SHOW TABLES', array()));

        if ($input->getOption('tables') === null) {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Select the tables to generate the crud (defaults all tables)',
                $getTables,
                implode(',', array_keys($getTables))
            );
            $question->setMultiselect(true);
            $tables_generate = $helper->ask($input, $output, $question);
        } else {
            $tables_in = explode(',', $input->getOption('tables'));
            $tables_generate = array();
            foreach ($tables_in as $table_in) {
                if (in_array($table_in, $getTables)) {
                    $tables_generate[] = $table_in;
                }
            }
        }

        // show tables selected
        $output->writeln('You have just selected: <comment>'.implode('</comment>, <comment>', $tables_generate).'</comment>');

        $tables = array();
        foreach ($tables_generate as $table_info) {
            $table_name = $table_info;
            $table_column = array();
            $table_form = array();

            $table_result = $this->get('db')->fetchAll(sprintf('DESC `%s`', $table_name), array());

            $primary_key = null;
            $primary_keys = 0;
            $primary_keys_auto = 0;

            array_map(function ($column) use (&$primary_keys, &$primary_keys_auto) {
                if ($column['Key'] === 'PRI') {
                    $primary_keys++;
                }
                if ($column['Extra'] == 'auto_increment') {
                    $primary_keys_auto++;
                }
            }, $table_result);

            if(!($primary_keys === 1 || ($primary_keys > 1 && $primary_keys_auto === 1))){
                continue;
            }

            foreach ($table_result as $column) {
                if ((($primary_keys > 1 && $primary_keys_auto == 1) and ($column['Extra'] == 'auto_increment')) or ($column['Key'] == "PRI")) {
                    $primary_key = $column['Field'];
                }

                $table_result_column = array(
                    'name' => $column['Field'],
                    'title' => ucfirst($column['Field']),
                    'primary' => $column['Field'] == $primary_key ? true : false,
                    'nullable' => $column['Null'] == 'NO' ? true : false,
                    'auto' => $column['Extra'] == 'auto_increment' ? true : false,
                    'type' => preg_replace('/\((\d+)\)$/', '', $column['Type']),
                    'lenght' => (int) preg_replace('/[^\d+]/', '', $column['Type']),
                );

                if (!in_array(strtolower($column['Field']), array('id', 'created', 'updated'))) {
                    switch ($table_result_column['type']) {
                        case 'text':
                        case 'tinytext':
                        case 'mediumtext':
                        case 'longtext':
                            $type_form = 'textarea';
                            $regex = '';
                            break;

                        case 'datetime':
                            $type_form = 'text';
                            $regex = '';
                            break;

                        default:
                            $type_form = 'text';
                            $regex = '';
                            break;
                    }

                    $table_form[] = array_merge($table_result_column, array(
                        'type' => $type_form,
                        'validation_regex' => $regex,
                    ));
                }
                $table_column[] = $table_result_column;
            }

            $tables[$table_name] = array(
                'primary_key' => $primary_key,
                'columns' => $table_column,
                'columns_form' => $table_form,
            );
        }

        $output->writeln('Generating');
        foreach ($tables as $table => $data) {
            $this->createController($table, $data);
            $this->createViews($table, $data);
            $this->createRoutes($table);
            $this->createMenu($table);
        }
    }

    private function createController($table, array $data)
    {
        $fs = new Filesystem();
        $dir_controller = realpath(__DIR__.'/../../Controller');

        $controller = $this->get('twig')->render('generator/controller.twig', array('table' => $table, 'data' => $data));
        $fs->dumpFile(sprintf('%s/%sController.php', $dir_controller, ucfirst($table)), $controller);
    }

    private function createViews($table, array $data)
    {
        $fs = new Filesystem();
        $dir_views = realpath(__DIR__.'/../../../views/');
        $dir_view = sprintf('%s/%s', $dir_views, $table);

        if ($fs->exists($dir_view) === false) {
            $fs->mkdir($dir_view, 0755);
        }

        $list_view = $this->get('twig')->render('generator/views/theme.twig', array('table' => $table, 'data' => $data));
        $fs->dumpFile(sprintf('%s/theme.twig', $dir_view), $list_view);

        $list_view = $this->get('twig')->render('generator/views/list.twig', array('table' => $table, 'data' => $data));
        $fs->dumpFile(sprintf('%s/list.twig', $dir_view), $list_view);

        $list_view = $this->get('twig')->render('generator/views/create.twig', array('table' => $table, 'data' => $data));
        $fs->dumpFile(sprintf('%s/create.twig', $dir_view), $list_view);

        $list_view = $this->get('twig')->render('generator/views/edit.twig', array('table' => $table, 'data' => $data));
        $fs->dumpFile(sprintf('%s/edit.twig', $dir_view), $list_view);
    }

    public function createRoutes($table)
    {
        $fs = new Filesystem();
        $file_routes = __DIR__.'/../../routes.php';

        if ($fs->exists($file_routes)) {
            $file_contents = array_map(function($line){ return preg_replace('/\n/', '', $line); }, file($file_routes));

            $table_routes = array();
            $exists = array(
                'index' => false,
                'list' => false,
                'create' => false,
                'edit' => false,
                'delete' => false,
            );

            foreach (array_keys($exists) as $route) {
                $lines_found = array_keys(preg_grep(sprintf('/\'%s::%s\'/i', $table, $route), $file_contents));
                $exists[$route] = count($lines_found) === 1;
            }

            $table_lower = strtolower($table);
            $table_camel = ucfirst($table);

            if ($exists['index'] === false) {
                $table_routes[] = "\$route->get(sprintf('/%s/{$table_lower}', \$app['security_path']), '{$table_camel}::index')->bind('{$table_lower}');";
            }
            if ($exists['list'] === false) {
                $table_routes[] = "\$route->get(sprintf('/%s/{$table_lower}/list', \$app['security_path']), '{$table_camel}::list')->bind('{$table_lower}_list');";
            }
            if ($exists['create'] === false) {
                $table_routes[] = "\$route->match(sprintf('/%s/{$table_lower}/create', \$app['security_path']), '{$table_camel}::create')->method('GET|POST')->bind('{$table_lower}_create');";
            }
            if ($exists['edit'] === false) {
                $table_routes[] = "\$route->match(sprintf('/%s/{$table_lower}/edit/{id}', \$app['security_path']), '{$table_camel}::edit')->method('GET|POST')->bind('{$table_lower}_edit');";
            }
            if ($exists['delete'] === false) {
                $table_routes[] = "\$route->get(sprintf('/%s/{$table_lower}/delete/{id}', \$app['security_path']), '{$table_camel}::delete')->bind('{$table_lower}_delete');";
            }

            $last_line = array_keys(preg_grep('/return/', $file_contents))[0];

            // Rewriting
            $rewriting = array();
            $line_blank = 0;
            foreach($file_contents as $line => $value) {

                // Add routes
                if(count($table_routes) > 0 && $last_line == $line) {
                    $rewriting[] = '// ' . $table_camel;
                    foreach($table_routes as $route_value) {
                        $rewriting[] = $route_value;
                    }
                    $rewriting[] = '';
                }
                if (strlen(trim($value)) === 0) {
                    $line_blank++;
                } else {
                    $line_blank = 0;
                }
                if ($line_blank <= 1) {
                    $rewriting[] = $value;
                }
            }

            $fs->dumpFile($file_routes, implode("\n", $rewriting));
        }
    }

    public function createMenu($table)
    {
        $fs = new Filesystem();
        $file_menus = __DIR__.'/../../../views/menu.twig';

        if ($fs->exists($file_menus)) {
            $file_contents = array_map(function($line){ return preg_replace('/\n/', '', $line); }, file($file_menus));

            $table_lower = strtolower($table);
            $table_camel = ucfirst($table);

            if (!preg_grep(sprintf('/\{\{([ ]*)path\(([ ]*)\'%s\'([ ]*)\)/', strtolower($table_lower)), $file_contents)) {
                $file_contents[] = '<li {% if menu_selected is defined and menu_selected == \'' . $table_lower . '\' %}class="active"{% endif %}>';
                $file_contents[] = "\t<a href=\"{{ path('{$table_lower}') }}\">";
                $file_contents[] = "\t\t<i class=\"fa fa-bars\"></i> <span>{$table_camel}</span>";
                $file_contents[] = "\t</a>";
                $file_contents[] = '</li>';
            }

            $fs->dumpFile($file_menus, implode("\n", $file_contents));
        }
    }
}
