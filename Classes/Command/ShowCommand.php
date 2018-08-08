<?php
namespace Ribase\RibaseConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml;


class ShowCommand extends Command
{

    protected $filename = PATH_site.'../configs/console/aliases.yml';

    protected function configure()
    {
        $this->addArgument('option', InputArgument::OPTIONAL, 'pass a option');
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

        $listType = $input->getArgument('list');

        switch ($listType) {
            case "help":

                $help = [
                    '<info>',
                    '*******************************************************',
                    '* typo3 show:aliases              shows all aliases   *',
                    '* typo3 show:aliases this         shows current alias *',
                    '* typo3 show:aliases help         shows help          *',
                    '*******************************************************',
                    '</info>',
                ];

                $output->writeln($help);

                break;
            case "this":
                $contents = Yaml\Yaml::parseFile($this->filename);

                foreach ($contents as $key => $value ){
                    if($value["path"] == PATH_site){
                        $output->writeln('<info>You are here: </info><question>'.$key.'</question>');
                    }

                }


                break;
            default:

                $output->writeln("Type 'show:aliases help' to find out more commands.");

                $output->writeln('Reading existing alias file');
                $contents = Yaml\Yaml::parseFile($this->filename);

                $aliasesOutput = array();

                foreach ($contents as $key => $value ){
                    $aliasesOutput[] = '<question>'.$key.':</question>';
                    foreach ($value as $key2 => $value2){
                        $aliasesOutput[] = '   <info>'.$key2.': </info><comment>'.$value2.'</comment>';
                    }

                }


                $output->writeln($aliasesOutput);
                break;
        }

        return 0;

    }
}