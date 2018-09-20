<?php
namespace Ribase\RibaseConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ribase\RibaseConsole\Service\DetermineServer;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseCommand extends Command
{

    protected function configure()
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'pass a action');
        $this->addArgument('from', InputArgument::OPTIONAL, 'set the server from');
        $this->addArgument('to', InputArgument::OPTIONAL, 'set the server to');
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

        $action = $input->getArgument('action');
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');

        $credentials = $GLOBALS['TYPO3_CONF_VARS']['DB']["Connections"]["Default"];

        $fromServer = $serverHelper->getServerForCommand($from);
        $fromPath = $serverHelper->getPathForCommand($from);
        $toServer = $serverHelper->getServerForCommand($to);
        $toPath = $serverHelper->getPathForCommand($to);
        $fromServerRsync = $serverHelper->getServerRsync($from);
        $toServerRsync = $serverHelper->getServerRsync($to);
        $filename = "database";

        switch ($action) {
            case "migrate":

                $migrated = $this->doMigrate($output);
                $output->writeln($migrated);

                break;
            case "dump":
                if (empty($fromServer)) {
                    $output->writeln('<error>no target set...u are a bad boy</error>');
                    return 500;
                }

                if (strpos($fromServer, '@')) {
                    exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; ../vendor/bin/typo3 database dumpthis"');
                } else {
                    exec('cd ' . PATH_site . '; mysqldump -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' -r ' . $filename . '.dump');
                }

                break;
            case "dumpthis":
                $output->writeln('<comment>Dump local database.</comment>');
                exec('mysqldump -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' -r ' . $filename . '.dump');

                break;
            case "importthis":
                $output->writeln('<comment>Import local database.</comment>');
                exec('mysql -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' < ' . $filename . '.dump');

                break;
            case "sync":

                $output->writeln('<comment>Sync database from '.$from.' to '.$to.'.</comment>');
                $output->writeln('<comment>Start to dump database.</comment>');
                if (strpos($from, '@')) {
                    exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; ../vendor/bin/typo3 database dumpthis"');
                } else {
                    exec('cd ' . PATH_site . '; mysqldump -u' . $credentials['user'] . ' -h' . $credentials['host'] . ' -p' . $credentials['password'] . ' ' . $credentials['dbname'] . ' -r ' . $filename . '.dump');
                }


                $output->writeln('<comment>Copy database from '.$from.' to '.$to.'.</comment>');
                exec('rsync -chavzP --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys ' . $fromServer . ':' . $fromPath . 'database.dump ' . $toServer . $toPath . 'database.dump');

                // if foreign, clean up!
                if (strpos($to, '@')) {
                    $output->writeln('<comment>Clean up on Server.</comment>');
                    exec('ssh ' . $fromServer . ' "cd ' . $fromPath . ' ; rm database.dump"');
                }


                $output->writeln('<comment>Import database to '.$to.'.</comment>');
                if(strpos($to, '@')){
                    exec('ssh '.$fromServer.' "cd '.$fromPath.' ; ../vendor/bin/typo3 database importthis"');
                }else {
                    exec('mysql -u'.$credentials['user'].' -h'.$credentials['host'].' -p'.$credentials['password'].' '.$credentials['dbname'].' < '.$filename.'.dump');
                }

                break;
        }

        return 0;

    }

    private function doMigrate(OutputInterface $output) {

        $databaseAnalyzerSuggestion = [];

        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $sqlStatements = $sqlReader->getCreateTableStatementArray($sqlReader->getTablesDefinitionString());
        $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);

        $addCreateChange = $schemaMigrationService->getUpdateSuggestions($sqlStatements);
        // Aggregate the per-connection statements into one flat array
        $addCreateChange = array_merge_recursive(...array_values($addCreateChange));

        if (!empty($addCreateChange['create_table'])) {
            $databaseAnalyzerSuggestion['addTable'] = [];
            foreach ($addCreateChange['create_table'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['addTable'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
            }
        }
        if (!empty($addCreateChange['add'])) {
            $databaseAnalyzerSuggestion['addField'] = [];
            foreach ($addCreateChange['add'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['addField'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
            }
        }
        if (!empty($addCreateChange['change'])) {
            $databaseAnalyzerSuggestion['change'] = [];
            foreach ($addCreateChange['change'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['change'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
                if (isset($addCreateChange['change_currentValue'][$hash])) {
                    $databaseAnalyzerSuggestion['change'][$hash]['current'] = $addCreateChange['change_currentValue'][$hash];
                }
            }
        }

        // Difference from current to expected
        $dropRename = $schemaMigrationService->getUpdateSuggestions($sqlStatements, true);
        // Aggregate the per-connection statements into one flat array
        $dropRename = array_merge_recursive(...array_values($dropRename));
        if (!empty($dropRename['change_table'])) {
            $databaseAnalyzerSuggestion['renameTableToUnused'] = [];
            foreach ($dropRename['change_table'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['renameTableToUnused'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
                if (!empty($dropRename['tables_count'][$hash])) {
                    $databaseAnalyzerSuggestion['renameTableToUnused'][$hash]['count'] = $dropRename['tables_count'][$hash];
                }
            }
        }
        if (!empty($dropRename['change'])) {
            $databaseAnalyzerSuggestion['renameTableFieldToUnused'] = [];
            foreach ($dropRename['change'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['renameTableFieldToUnused'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
            }
        }
        if (!empty($dropRename['drop'])) {
            $databaseAnalyzerSuggestion['deleteField'] = [];
            foreach ($dropRename['drop'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['deleteField'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
            }
        }
        if (!empty($dropRename['drop_table'])) {
            $databaseAnalyzerSuggestion['deleteTable'] = [];
            foreach ($dropRename['drop_table'] as $hash => $statement) {
                $databaseAnalyzerSuggestion['deleteTable'][$hash] = [
                    'hash' => $hash,
                    'statement' => $statement,
                ];
                if (!empty($dropRename['tables_count'][$hash])) {
                    $databaseAnalyzerSuggestion['deleteTable'][$hash]['count'] = $dropRename['tables_count'][$hash];
                }
            }
        }


        if(empty($databaseAnalyzerSuggestion)){
            $output->writeln('<info>No actions needed or can be performed</info>');
            return 0;
        }


        $executedStatements[] = 'These statements are executed: ';
        $count = 0;
        foreach ($databaseAnalyzerSuggestion as $migrateType => $content) {
            if($migrateType) {
                foreach ($content as $key => $value ){
                    $statementHashesToPerform[$value["hash"]] = 1;
                    $executedStatements[] = '<info>'.$value["statement"].'</info>';
                    $count++;
                }
            }
        }
        $executedStatements[] = 'Total count: '.$count;


        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $sqlStatements = $sqlReader->getCreateTableStatementArray($sqlReader->getTablesDefinitionString());
        $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);
        $results = $schemaMigrationService->migrate($sqlStatements, $statementHashesToPerform);

        return $executedStatements;
    }
}