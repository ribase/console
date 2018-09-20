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

class DatabaseDumpCommand extends Command
{

    protected function configure()
    {
        $this->addArgument('from', InputArgument::OPTIONAL, 'set the server from');
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

        $credentials = $GLOBALS['TYPO3_CONF_VARS']['DB']["Connections"]["Default"];

        $fromServer = $serverHelper->getServerForCommand($from);
        $fromPath = $serverHelper->getPathForCommand($from);
        $filename = "database";

        if (empty($fromServer)) {
            $output->writeln('<error>no target set...u are a bad boy</error>');
            return 500;
        }

        if (strpos($fromServer, '@')) {
            exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; ../vendor/bin/typo3 database:dumpthis"');
        } else {
            exec('cd ' . PATH_site . '; mysqldump -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' -r ' . $filename . '.dump');
        }


        return 0;

    }
}