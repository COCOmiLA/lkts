<?php

use common\components\secureUrlManager\SecureUrlManager;

$params = array_merge(
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'moderator-api',
    'basePath' => dirname(__DIR__),
    'modules' => [
        'moderator' => [
            'basePath' => '@api/modules/moderator',
            'class' => \api\modules\moderator\Module::class
        ]
    ],
    'components' => [
        'user' => [
            'identityClass' => \common\models\User::class,
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled(),
            ],
            'enableAutoLogin' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'maskVars' => \common\models\logs\LogVarsExcluder::excludeVars()
                ],
            ],
        ],
        'urlManager' => [
            'class' => \yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'request' => [
            'class' => \common\components\Request::class,
            'baseUrl' => '/api',
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
    ],
    'params' => $params,
];



