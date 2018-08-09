<?php
return [
    'print:something' => [
        'class' => Ribase\RibaseConsole\Command\ExampleCommand::class
    ],
    'init:alias' => [
        'class' => Ribase\RibaseConsole\Command\InitAliasCommand::class
    ],
    'show:aliases' => [
        'class' => Ribase\RibaseConsole\Command\ShowCommand::class
    ],
    'fileadmin' => [
        'class' => Ribase\RibaseConsole\Command\FileadminCommand::class
    ],
    'packagestates' => [
        'class' => Ribase\RibaseConsole\Command\PackageStatesCommand::class
    ],
    'database' => [
        'class' => Ribase\RibaseConsole\Command\DatabaseCommand::class
    ]
];