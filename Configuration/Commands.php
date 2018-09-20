<?php
return [
    'print:something' => [
        'class' => Ribase\RibaseConsole\Command\ExampleCommand::class
    ],
    'alias:init' => [
        'class' => Ribase\RibaseConsole\Command\InitAliasCommand::class
    ],
    'alias:show' => [
        'class' => Ribase\RibaseConsole\Command\ShowCommand::class
    ],
    'fileadmin:sync' => [
        'class' => Ribase\RibaseConsole\Command\FileadminCommand::class
    ],
    'packagestates' => [
        'class' => Ribase\RibaseConsole\Command\PackageStatesCommand::class
    ],
    'database:dumpthis' => [
        'class' => Ribase\RibaseConsole\Command\DatabaseDumpThisCommand::class
    ],
    'database:importthis' => [
        'class' => Ribase\RibaseConsole\Command\DatabaseImportThisCommand::class
    ],
    'database:sync' => [
        'class' => Ribase\RibaseConsole\Command\DatabaseSyncCommand::class
    ],
    'database:dump' => [
        'class' => Ribase\RibaseConsole\Command\DatabaseDumpCommand::class
    ]
];