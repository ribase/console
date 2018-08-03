<?php
namespace Ribase\RibaseConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrintCommand extends Command
{
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $words = array('Hello', 'How are you?', 'Nice to meet you');
        $output->writeln($words[array_rand($words, 1)]);

        return 0;

    }
}