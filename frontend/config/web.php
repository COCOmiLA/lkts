<?php

use common\components\secureUrlManager\SecureUrlManager;

$config = [
    'homeUrl' => Yii::getAlias('@frontendUrl'),
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute' => 'site/index',
    'modules' => [
        'user' => [
            'class' => 'frontend\modules\user\Module'
        ],
        'api' => [
            'class' => 'frontend\modules\api\Module',
            'modules' => [
                'v1' => 'frontend\modules\api\v1\Module'
            ]
        ],
        'oauth2' => [
            'class' => 'filsh\yii2\oauth2server\Module',
            'tokenParamName' => 'accessToken',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'common\models\User',
                'authorization_code' => 'frontend\models\AuthorizationCodeStorage',
                'client_credentials' => 'frontend\models\ClientCredentialsStorage',
            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ],
                'authorization_code' => [
                    'class' => 'OAuth2\GrantType\AuthorizationCode',
                ]
            ]
        ],
    ],
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error'
        ],
        'request' => [
            'class' => \common\components\Request::class,
            'cookieValidationKey' => getenv('FRONTEND_COOKIE_VALIDATION_KEY'),
            'baseUrl' => '',
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled(),
            ],
            'identityClass' => 'common\models\User',
            'loginUrl' => ['/user/sign-in/login'],
            'enableAutoLogin' => true,
            'as afterLogin' => 'common\behaviors\LoginTimestampBehavior'
        ],
    ]
];

if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'crud' => [
                'class' => 'yii\gii\generators\crud\Generator',
                'messageCategory' => 'frontend'
            ]
        ]
    ];
}

if (YII_ENV_PROD) {
    
    $config['bootstrap'] = ['maintenance'];
    $config['components']['maintenance'] = [
        'class' => 'common\components\maintenance\Maintenance',
        'enabled' => function ($app) {
            
            return $app->keyStorage->tablesExists() && $app->keyStorage->get('frontend.maintenance') === 'enabled';
        }
    ];
}

return $config;
