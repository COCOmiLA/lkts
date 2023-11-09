<?php

use common\components\secureUrlManager\SecureUrlManager;

$config = [
    'homeUrl' => Yii::getAlias('@backendUrl'),
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute' => 'timeline-event/index',
    'controllerMap' => [],
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'class' => \common\components\Request::class,
            'baseUrl' => '/admin',
            'cookieValidationKey' => getenv('BACKEND_COOKIE_VALIDATION_KEY'),
            'csrfParam' => '_backendCSRF',
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => \common\models\User::class,
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled(),
            ],
            'loginUrl' => ['sign-in/login'],
            'enableAutoLogin' => true,
            'as afterLogin' => \common\behaviors\LoginTimestampBehavior::class
        ],
    ],
    'modules' => [
        'i18n' => [
            'class' => 'backend\modules\i18n\Module',
            'defaultRoute' => 'i18n-message/index'
        ]
    ],
    'as globalAccess' => [
        'class' => \common\behaviors\GlobalAccessBehavior::class,
        'rules' => [
            [
                'controllers' => ['sign-in'],
                'allow' => true,
                'roles' => ['?'],
                'actions' => ['login']
            ],
            [
                'controllers' => ['sign-in'],
                'allow' => true,
                'roles' => ['@'],
                'actions' => ['logout']
            ],
            [
                'controllers' => ['site'],
                'allow' => true,
                'roles' => ['?', '@'],
                'actions' => ['error']
            ],
            [
                'controllers' => ['debug/default'],
                'allow' => true,
                'roles' => ['?'],
            ],
            [
                'controllers' => ['user'],
                'allow' => true,
                'roles' => ['administrator'],
            ],
            [
                'controllers' => ['user'],
                'allow' => false,
            ],
            [
                'allow' => true,
                'roles' => ['manager'],
            ]
        ]
    ]
];

return $config;
