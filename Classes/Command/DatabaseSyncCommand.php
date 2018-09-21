<?php

namespace Ribase\RibaseConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ribase\RibaseConsole\Helper\DetermineServer;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseSyncCommand extends Command
{

    protected function configure()
    {
        $this->addArgument('from', InputArgument::REQUIRED, 'set the server from');
        $this->addArgument('to', InputArgument::REQUIRED, 'set the server to');
    }


    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $serverHelper = new DetermineServer();

        $from = $input->getArgument('from');
        $to = $input->getArgument('to');

        $credentials = $GLOBALS['TYPO3_CONF_VARS']['DB']["Connections"]["Default"];

        $fromServer = $serverHelper->getServerForCommand($from);
        $fromPath = $serverHelper->getPathForCommand($from);
        $toServer = $serverHelper->getServerForCommand($to);
        $toPath = $serverHelper->getPathForCommand($to);
        $filename = "database";

        $output->writeln('<comment>Sync database from ' . $from . ' to ' . $to . '.</comment>');
        $output->writeln('<comment>Start to dump database.</comment>');
        if (strpos($from, '@')) {
            exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; ../vendor/bin/typo3 database:dumpthis"');
        } else {
            exec('cd ' . PATH_site . '; mysqldump  --verbose -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' -r ' . $filename . '.dump');
        }


        $output->writeln('<comment>Copy database from ' . $from . ' to ' . $to . '.</comment>');
        exec('rsync -chavzP --progress --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys ' . $fromServer . ':' . $fromPath . 'database.dump ' . $toServer . $toPath . 'database.dump');

        // if foreign, clean up!
        if (strpos($to, '@')) {
            $output->writeln('<comment>Clean up on Server.</comment>');
            exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; rm database.dump"');
        }


        $output->writeln('<comment>Import database to ' . $to . '.</comment>');
        if (strpos($to, '@')) {
            exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; ../vendor/bin/typo3 database:importthis"');
        } else {
            exec('mysql --verbose -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' < ' . $filename . '.dump');
        }

        return 0;

    }
}