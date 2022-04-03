<?php

namespace WS\Cli\Command\Install;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;

class Uninstall extends Command
{

    protected static $defaultName = 'install:uninstall';

    protected function configure(): void
    {
        $this
            ->setHelp('Uninstall service.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $app = App::getInstance();
            $db = $app->db();
            $tables = $app->tables();

            foreach ($tables as $tableName => $table)
            {
                $db->drop($tableName);
            }

            return Command::SUCCESS;
        }
        catch (Exception)
        {
            $output->writeln('Encountered an error while running install processes.');
            return Command::FAILURE;
        }
    }

}