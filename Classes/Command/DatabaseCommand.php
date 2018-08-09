<?php
namespace Ribase\RibaseConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseCommand extends Command
{

    /**
     * @var string
     */
    protected $filename = PATH_site.'../configs/console/aliases.yml';

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

        $contents = Yaml::parseFile($this->filename);
        $action = $input->getArgument('action');
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');

        $credentials = $GLOBALS['TYPO3_CONF_VARS']['DB']["Connections"]["Default"];

        $fromServer = $this->getServer($from, $contents);
        $toServer = $this->getServer($to, $contents);
        $fromServerRsync = $this->getServerRsync($from, $contents);
        $toServerRsync = $this->getServerRsync($to, $contents);
        $filename = str_replace('@', '', $from);

        switch ($action) {
            case "migrate":

                $migrated = $this->doMigrate($output);
                $output->writeln($migrated);

                break;
            case "dump":
                if(empty($fromServer)){
                    $output->writeln('<error>no target set...u are a bad boy</error>');
                    return 500;
                }

                if(strpos($fromServer, '@')){
                    exec('ssh '.$fromServer.' "mysqldump -u'.$credentials['user'].'-h'.$credentials['host'].' -p'.$credentials['password'].' '.$credentials['dbname'].' -r '.$filename.'.dump"');
                }else {
                    exec('cd '.PATH_site.'; mysqldump -u'.$credentials['user'].' -h'.$credentials['host'].' -p'.$credentials['password'].' '.$credentials['dbname'].' -r '.$filename.'.dump');
                }

                break;
            case "dumpthis":

                exec('mysqldump -u'.$credentials['user'].' -h'.$credentials['host'].' -p'.$credentials['password'].' '.$credentials['dbname'].' -r '.$filename.'.dump');

                break;
            case "importthis":
                exec('mysql -u'.$credentials['user'].' -h'.$credentials['host'].' -p'.$credentials['password'].' '.$credentials['dbname'].' < '.$filename.'.dump');

                break;
            case "sync":
                if(empty($toServer) || empty($fromServer)){
                    $output->writeln('<error>no target set...u are a bad boy</error>');
                    return 500;
                }

                if(strpos($fromServer, '@')) {
                    $deleteFile = '--remove-source-files';
                }else {
                    $deleteFile = '';
                }

                $output->writeln('<comment>exporting database on '.$from.'</comment>');

                if(strpos($fromServer, '@')){
                    exec('ssh '.$fromServer.'../vendor/bin/typo3 dumpthis '.$from, $out, $err);
                }else {
                    exec('../vendor/bin/typo3 database dumpthis '.$from, $out, $err);
                }

                if($err == 1) {
                    $output->writeln('<error>'.$out.'</error>');
                    return 500;
                }


                $output->writeln('<comment>sync database from '.$from.' to '.$to.'</comment>');

                exec('rsync -chavzP '.$deleteFile.' --stats --exclude=_processed_ --exclude=_temp_ --exclude=log --exclude=sys '.$fromServerRsync.$filename.'.dump '.$toServerRsync.$filename.'.dump');


                $output->writeln('<comment>importing database on '.$to.'</comment>');
                if(strpos($fromServer, '@')){
                    exec('ssh '.$fromServer.' ../vendor/bin/typo3 database importthis '.$from);
                }else {
                    exec('../vendor/bin/typo3 database importthis '.$from);
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
    private function getServer($alias, $contents) {

        foreach ($contents as $key => $value ){
            foreach ($value as $key2 => $value2){
                if($value2 == $alias) {
                    if($value["type"] == 'foreign') {
                        $rsyncString = $value["user"].'@'.$value["server"].' '.$value["path"];
                    }else {
                        $rsyncString = $value["path"];
                    }
                }
            }
        }

        return $rsyncString;
    }

    private function getServerRsync($alias, $contents) {

        foreach ($contents as $key => $value ){
            foreach ($value as $key2 => $value2){
                if($value2 == $alias) {
                    if($value["type"] == 'foreign') {
                        $rsyncString = $value["user"].'@'.$value["server"].':'.$value["path"];
                    }else {
                        $rsyncString = $value["path"];
                    }
                }
            }
        }

        return $rsyncString;
    }
}