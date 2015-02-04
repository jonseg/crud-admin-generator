<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Generator\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GeneratorCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('crud:generator')
            ->setDescription('generator crud')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $getTables = array_map(function ($value) { return array_values($value)[0]; }, $this->get('db')->fetchAll('SHOW TABLES', array()));

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the tables to generate the crud (defaults all tables)',
            $getTables,
            implode(',', array_keys($getTables))
        );
        $question->setMultiselect(true);
        $tables_generate = $helper->ask($input, $output, $question);

        // show tables selected
        $output->writeln('You have just selected: <comment>'.implode('</comment>, <comment>', $tables_generate).'</comment>');

        $dbTables = array();
        foreach ($tables_generate as $table_info) {
            $info_table = array(
                'name' => $table_info, // define table
                'columns' => $this->get('db')->fetchAll(sprintf('SHOW COLUMNS FROM `%s`', $table_info), array()), // return the table columns
            );

            // Find relationship between the tables
            foreach ($info_table['columns'] as &$column) {
                $column['Relation'] = $this->get('db')->fetchAll(sprintf(
                    'SELECT
                        IF(`TABLE_NAME` = \'%s\' AND `COLUMN_NAME` = \'%s\', `REFERENCED_TABLE_NAME`, `TABLE_NAME`) AS `table`,
                        IF(`TABLE_NAME` = \'%s\' AND `COLUMN_NAME` = \'%s\', `REFERENCED_COLUMN_NAME`, `COLUMN_NAME`) AS `column`,
                        CONSTRAINT_NAME as name
                    FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
                    WHERE `TABLE_SCHEMA` = SCHEMA()
                    AND `REFERENCED_TABLE_NAME` IS NOT NULL
                    AND ((`TABLE_NAME` = \'%s\' AND `COLUMN_NAME` = \'%s\') OR (`REFERENCED_TABLE_NAME` = \'%s\' AND `REFERENCED_COLUMN_NAME`= \'%s\'))',
                    $table_info,
                    $column['Field'],
                    $table_info,
                    $column['Field'],
                    $table_info,
                    $column['Field'],
                    $table_info,
                    $column['Field']
                ));
            }

            $dbTables[] = $info_table;
        }

        $output->writeln('Generating');
        foreach ($dbTables as $table) {
            $output->writeln($table['name'].str_pad('<info>[OK]</info>', (50 - strlen($table['name'])), " ", STR_PAD_LEFT));
        }
    }
}
