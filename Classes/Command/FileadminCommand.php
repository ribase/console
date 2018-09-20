<?php
namespace Ribase\RibaseConsole\Command;

use Ribase\RibaseConsole\Service\DetermineServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FileadminCommand extends Command
{

    protected $filename = PATH_site.'../configs/console/aliases.yml';

    protected function configure()
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'pass a action');
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

        $action = $input->getArgument('action');
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');
        $delete = $input->getArgument('delete');

        switch ($action) {
            case "sync":

                $fromServer = $serverHelper->getServerRsync($from);
                $toServer = $serverHelper->getServerRsync($to);

                if(!$toServer) {
                    $output->writeln('<error>No target...well that was retard.</error>');
                    return 500;
                }

                $output->writeln('<comment>Sync Fileadmin from'.$from.' to '.$to.'.</comment>');

                if($delete == 'delete'){
                    exec('rsync -chavzP --delete --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromServer.'fileadmin/ '.$toServer.'fileadmin/');
                }else {
                    exec('rsync -chavzP  --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromServer.'fileadmin/ '.$toServer.'fileadmin/');
                }

                break;
            case "size":
                $fromServer = $serverHelper->getServer($from);

                if(strpos($fromServer, 'ssh')){
                    exec('ssh '.$fromServer.' du -sh fileadmin');
                }else {
                    exec('du -sh '.$fromServer.'fileadmin', $result);
                    $output->writeln($result);
                }
                break;
        }

        return 0;
    }

}