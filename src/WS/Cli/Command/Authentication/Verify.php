<?php

namespace WS\Cli\Command\Authentication;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;
use WS\Repository\Authentication;

class Verify extends Command
{

    protected static $defaultName = 'authentication:verify';

    protected function configure(): void
    {
        $this
            ->setHelp('Create an authentication credential')
            ->addArgument('user_id', InputArgument::REQUIRED, 'User ID')
            ->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Authentication $authenticationRepo */
        $authenticationRepo = App::getInstance()->repository('Authentication');
        $verified = $authenticationRepo->verifyCredential(
            intval($input->getArgument('user_id')), $input->getArgument('password'));

        if ($verified)
        {
            $output->writeln('Authenticated');
        }
        else
        {
            $output->writeln('Authentication failed');
        }

        return Command::SUCCESS;
    }

}