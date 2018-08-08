<?php
namespace Ribase\RibaseConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml;

class InitAliasCommand extends Command
{

    protected $filename = PATH_site.'../configs/console/aliases.yml';

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

        $output->writeln('
                                       __                              _      __  
          _________  ____  _________  / /__     ____ ___  ____ _____ _(_)____/ /__
         / ___/ __ \/ __ \/ ___/ __ \/ / _ \   / __ `__ \/ __ `/ __ `/ / ___/ //_/
        / /__/ /_/ / / / (__  ) /_/ / /  __/  / / / / / / /_/ / /_/ / / /__/ ,<   
        \___/\____/_/ /_/____/\____/_/\___/  /_/ /_/ /_/\__,_/\__, /_/\___/_/|_|  
                                                             /____/               
        ');


        $helper = $this->getHelper('question');

        if (file_exists($this->filename)) {
            $output->writeln('Reading existing alias file');
            $contents = Yaml\Yaml::parseFile($this->filename);
        } else {
            $output->writeln('Before we start, i need to create some files');

            mkdir(PATH_site.'../configs/', 0755, true);
            mkdir(PATH_site.'../configs/console/', 0755, true);
            touch($this->filename);
        }



        $question = new ChoiceQuestion(
            'Would you like to add a new alias?',
            array('yes', 'no', 'help'),
            '1'
        );
        $question->setMultiselect(false);

        $aliasCreation = $helper->ask($input, $output, $question);

        if($aliasCreation == 'yes'){

            $result = $this->createAlias($input,$output);

            $first_key = key($result);

            $contents[$first_key] = $result[$first_key];

            $yaml = Yaml\Yaml::dump($contents);
            file_put_contents($this->filename, $yaml);



        }else if($aliasCreation == 'help') {

            $output->writeln('You have just selected: ' . $aliasCreation);

        }else {
            return 0;
        }

        return 0;

    }

    private function createAlias($input, $output) {
        $helper = $this->getHelper('question');


        /* get alias name */
        $questionAliasName = new ChoiceQuestion(
            'Would you like to add a new alias?',
            array('local', 'dev', 'test', 'live'),
            '1'
        );
        $questionAliasName->setMultiselect(false);
        $aliasName = $helper->ask($input, $output, $questionAliasName);


        /* get alias path */
        $question = new Question('Name the path to your installation eg. /var/www/emboss.ch/: ', '/var/www/emboss.ch/');
        $aliasPath = $helper->ask($input, $output, $question);

        /* get alias type */
        if($aliasName == 'local'){
            $aliasType = 'local';
        }else {
            $aliasType = 'foreign';
        }

        if($aliasType == 'foreign'){
            /* get alias ssh user */
            $question = new Question('SSH User on webserver: ', 'www-emboss');
            $userSSH = $helper->ask($input, $output, $question);
        }else {
            $userSSH = 'local';
        }

        if($aliasType == 'foreign'){
            /* get alias ssh user */
            $question = new Question('Tell me your server address (eg. cabvs070.nine.ch): ', 'nine.ch');
            $serverName = $helper->ask($input, $output, $question);
        }else {
            $serverName = 'local';
        }

        $alias = [
            'name' =>   '@'.$aliasName,
            'pathInternal' =>   $aliasPath,
            'type' =>   $aliasType,
            'user' =>   $userSSH,
            'server' => $serverName
        ];

        $aliasArray[$aliasName] = $alias;


        return $aliasArray;
    }
}