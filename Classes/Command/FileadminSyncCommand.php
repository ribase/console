<?php
namespace Ribase\RibaseConsole\Command;

use Ribase\RibaseConsole\Helper\DetermineServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FileadminSyncCommand extends Command
{

    protected $filename = PATH_site.'../configs/console/aliases.yml';

    protected function configure()
    {
        $this->addArgument('from', InputArgument::REQUIRED, 'set the server from');
        $this->addArgument('to', InputArgument::REQUIRED, 'set the server to');
        $this->addArgument('delete', InputArgument::OPTIONAL, 'set delete');
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
        $to = $input->getArgument('to');
        $delete = $input->getArgument('delete');


        $fromServer = $serverHelper->getServerForCommand($from);
        $fromPath = $serverHelper->getPathForCommand($from);
        $toServer = $serverHelper->getServerForCommand($to);
        $toPath = $serverHelper->getPathForCommand($to);

        if(!$toServer) {
            $output->writeln('<error>No target...well that was retard.</error>');
            return 500;
        }

        $output->writeln('<comment>Sync Fileadmin from'.$from.' to '.$to.'.</comment>');

        if($delete == 'delete'){
            if (strpos($from, '@') === 0) {
                exec('ssh -A ' . $fromServer . ' rsync -chavzP --delete --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromPath.'fileadmin/ '.$toServer . ':' . $toPath . 'fileadmin/');
            } else {
                exec('rsync -chavzP --delete --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromPath.'fileadmin/ '.$toServer . ':' . $toPath . 'fileadmin/');
            }
        }else {
            if (strpos($from, '@') === 0) {
                exec('ssh -A ' . $fromServer . ' rsync -chavzP  --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromPath.'fileadmin/ '.$toServer . ':' . $toPath . 'fileadmin/');
            } else {
                exec('rsync -chavzP  --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromPath.'fileadmin/ '.$toServer . ':' . $toPath . 'fileadmin/');
            }
        }
        return 0;
    }

}