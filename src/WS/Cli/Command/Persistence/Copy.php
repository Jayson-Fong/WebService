<?php

namespace WS\Cli\Command\Persistence;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WS\App;
use WS\Persistence\Persistence;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Copy extends Command
{

    protected static $defaultName = 'persistence:copy';

    protected function configure(): void
    {
        $this
            ->setHelp('Copy from a previous persistence provider to the current.')
            ->addArgument('provider', InputArgument::REQUIRED, 'Persistence Provider');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $app = App::getInstance();

            /** @var Persistence $oldProvider */
            $oldProvider = new ('WS\Persistence\\' . ucfirst($input->getArgument('provider')))($app);
            $keys = $oldProvider->keys();
            $newProvider = $app->persistence();

            foreach ($keys as $key)
            {
                $newProvider->put($key, $oldProvider->get($key), false);
                if ($input->getOption('verbose'))
                {
                    $output->writeln('Saved Key ' . $key);
                }
            }
            $newProvider->save();

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