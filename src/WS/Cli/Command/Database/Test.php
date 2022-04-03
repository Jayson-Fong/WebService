<?php

namespace WS\Cli\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;

class Test extends Command
{

    protected static $defaultName = 'database:test';

    protected function configure(): void
    {
        $this
            ->setHelp('Test the database connection.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            App::getInstance()->db();
            $output->writeln('OK');
            return Command::SUCCESS;
        }
        catch (Exception $e)
        {
            $output->writeln('ERROR: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

}