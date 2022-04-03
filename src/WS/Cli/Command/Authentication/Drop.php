<?php

namespace WS\Cli\Command\Authentication;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;
use WS\Repository\Authentication;

class Drop extends Command
{

    protected static $defaultName = 'authentication:drop';

    protected function configure(): void
    {
        $this
            ->setHelp('Drop an authentication credential')
            ->addArgument('user_id', InputArgument::REQUIRED, 'User ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Authentication $authenticationRepo */
        $authenticationRepo = App::getInstance()->repository('Authentication');
        $authenticationRepo->dropCredential(intval($input->getArgument('user_id')));

        $output->writeln('Done!');
        return Command::SUCCESS;
    }

}