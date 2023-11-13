<?php
return [
    'id' => 'frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['student', 'abiturient'],
    'components' => [
        'urlManager' => require(__DIR__ . '/_urlManager.php'),
    ],
];
