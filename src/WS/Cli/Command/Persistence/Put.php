<?php

namespace WS\Cli\Command\Persistence;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Put extends Command
{

    protected static $defaultName = 'persistence:put';

    protected function configure(): void
    {
        $this
            ->setHelp('Write a value to persistence storage.')
            ->addArgument('key', InputArgument::REQUIRED, 'Key')
            ->addArgument('value', InputArgument::REQUIRED, 'Value');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            App::getInstance()->persistence()->put($input->getArgument('key'), $input->getArgument('value'));
            $output->writeln('Successfully wrote to persistence');
            return Command::SUCCESS;
        }
        catch (Exception)
        {
            $output->writeln('Encountered an error while while writing to persistence.');
            return Command::FAILURE;
        }
    }

}