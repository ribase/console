<?php
namespace Ribase\RibaseConsole\Command;

use Ribase\RibaseConsole\Utility\CreatePackageStatesUtility;
use Ribase\RibaseConsole\Utility\PackageManagerUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class PackageStatesCommand extends Command
{

    /**
     * Object Manager
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $filename = PATH_site.'../configs/console/aliases.yml';


    protected function configure()
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'pass a action');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @param array $frameworkExtensions TYPO3 system extensions that should be marked as active. Extension keys separated by comma.
     * @param array $excludedExtensions Extensions which should stay inactive. This does not affect provided framework extensions or framework extensions that are required or part as minimal usable system.
     * @param bool $activateDefault (DEPRECATED) If true, <code>typo3/cms</code> extensions that are marked as TYPO3 factory default, will be activated, even if not in the list of configured active framework extensions.
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output, array $frameworkExtensions = [], array $excludedExtensions = [], $activateDefault = false)
    {
        //$contents = Yaml::parseFile($this->filename);
        $action = $input->getArgument('action');

        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        switch ($action) {
            case "update":
                /** @var \TYPO3\CMS\Core\Package\FailsafePackageManager $packageManager */
                $packageManager = \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->getEarlyInstance(\TYPO3\CMS\Core\Package\PackageManager::class);

                // Activate all packages required for a minimal usable system
                $packages = $packageManager->getAvailablePackages();
                foreach ($packages as $package) {
                    /** @var $package \TYPO3\CMS\Core\Package\PackageInterface */
                    if ($package instanceof \TYPO3\CMS\Core\Package\PackageInterface && $package->getPackageKey()) {
                        $packageManager->activatePackage($package->getPackageKey());
                        $packagesList[] = $package->getPackageKey();
                    }
                }

                $output->writeln("<comment>Activated packages:</comment> <info>".implode(',',$packagesList)."</info>");

                break;
        }

        return 0;
    }

}