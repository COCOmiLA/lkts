<?php

use geoffry304\enveditor\components\EnvComponent;
use geoffry304\enveditor\Module;

return [
    'id' => 'backend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['student'],
    'components' => [
        'urlManager' => require(__DIR__ . '/_urlManager.php'),
        'env' => [
            'class' => EnvComponent::class,
            'autoBackup' => true,
            'backupPath' => 'backups',
        ],
    ],
];
