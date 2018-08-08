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
    'install' => [
        'class' => Ribase\RibaseConsole\Command\InstallCommand::class
    ],
    'database' => [
        'class' => Ribase\RibaseConsole\Command\DatabaseCommand::class
    ]
];