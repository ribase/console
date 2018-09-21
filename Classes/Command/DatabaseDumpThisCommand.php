<?php
namespace Ribase\RibaseConsole\Command;

use Ribase\RibaseConsole\Helper\DatabaseExcludes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ribase\RibaseConsole\Helper\DetermineServer;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseDumpThisCommand extends Command
{

    protected function configure()
    {
        $this->addArgument('options', InputArgument::OPTIONAL, 'available options are full, minimal, noCache');
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

        $options = $input->getArgument('options');
        $credentials = $GLOBALS['TYPO3_CONF_VARS']['DB']["Connections"]["Default"];
        $filename = "database";

        if(!empty($options)) {
            if($options === "noCache"){
                $excludeService = new DatabaseExcludes();
                $excludes = $excludeService->createExcludes($options,$credentials['dbname']);

            }elseif($options === "minimal"){
                $includeService = new DatabaseExcludes();
                $includes = $includeService->createincludes($options,$credentials['dbname']);

            }else {
                $output->writeln('<error>Option not found...Exit!</error>');

            }
        }
        $output->writeln('<comment>Dump local database.</comment>');
        exec('mysqldump -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' '.$excludes.$includes.' -r ' . $filename . '.dump');

        return 0;

    }

}