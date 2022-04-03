<?php

namespace WS\Cli\Command\Persistence;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Clear extends Command
{

    protected static $defaultName = 'persistence:clear';

    protected function configure(): void
    {
        $this
            ->setHelp('Clear persistence storage.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            App::getInstance()->persistence()->clear();
            $output->writeln('Cleared Persistence!');
            return Command::SUCCESS;
        }
        catch (Exception)
        {
            $output->writeln('Encountered an error while while clearing persistence.');
            return Command::FAILURE;
        }
    }

}