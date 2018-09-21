<?php
namespace Ribase\RibaseConsole\Command;

use Ribase\RibaseConsole\Helper\DetermineServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FileadminSizeCommand extends Command
{

    protected $filename = PATH_site.'../configs/console/aliases.yml';

    protected function configure()
    {
        $this->addArgument('from', InputArgument::REQUIRED, 'set the server from');
        $this->addArgument('to', InputArgument::OPTIONAL, 'set the server to');
        $this->addArgument('delete', InputArgument::OPTIONAL, 'set delete flag');
    }

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

        $serverHelper = new DetermineServer();

        $from = $input->getArgument('from');

        $fromServer = $serverHelper->getServerForCommand($from);
        $fromPath = $serverHelper->getPathForCommand($from);


        if(strpos($from, '@') === 0 ){
            exec('ssh '.$fromServer.' "cd '.$fromPath.' ; du -hs fileadmin"', $result);
            $output->writeln($result);
        }else {
            exec('du -sh '.$fromServer.'fileadmin', $result);
            $output->writeln($result);
        }


        return 0;
    }

}