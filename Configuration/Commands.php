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