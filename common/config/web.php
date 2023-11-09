<?php

use common\components\tree\TreeParserComponent;
use himiklab\yii2\recaptcha\ReCaptchaConfig;
use yii\web\AssetManager;

$config = [
    'modules' => [
        'student' => [
            'class' => 'common\modules\student\Module',
            'components' => [
                'authManager' => [
                    'class' => 'common\modules\student\components\AuthManager',
                    'serviceUrl' => getenv('SERVICE_URI') . 'Authorization/Passwords/Passwords',
                    'login' => getenv("STUDENT_LOGIN"),
                    'password' => getenv("STUDENT_PASSWORD"),
                ]
            ]
        ],
        'abiturient' => [
            'class' => 'common\modules\abiturient\Module',
            'modules' => [
                'admission' => [
                    'class' => 'common\modules\abiturient\modules\admission\Module',
                    'components' => [
                        'admissionLoader' => [
                            'class' => 'common\modules\abiturient\modules\admission\components\admissionLoader',
                        ]
                    ]
                ]
            ]
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module'
        ]

    ],
    'components' => [
        'reCaptcha' => [
            'class' => ReCaptchaConfig::class,

            
            'siteKeyV2' => (getenv('SITE_KEY_V2') == false) ? '73' : getenv('SITE_KEY_V2'),
            'secretV2' => (getenv('SERVER_KEY_V2') == false) ? '73' : getenv('SERVER_KEY_V2'),

            
            'siteKeyV3' => (getenv('SITE_KEY_V3') == false) ? '73' : getenv('SITE_KEY_V3'),
            'secretV3' => (getenv('SERVER_KEY_V3') == false) ? '73' : getenv('SERVER_KEY_V3'),
        ],
        'assetManager' => [
            'class' => AssetManager::class,
            'linkAssets' => !empty(getenv('LINK_ASSETS_ENABLE')) && getenv('LINK_ASSETS_ENABLE') === 'true',
            'appendTimestamp' => YII_ENV_DEV
        ],
        'treeParser' => [
            'class' => TreeParserComponent::class,
        ],
        'portfolioTable' => [
            'class' => \common\components\tableForm\PortfolioTableComponent::class
        ],
    ],
    'as locale' => [
        'class' => \common\behaviors\LocaleBehavior::class,
        'enablePreferredLanguage' => true
    ],
];

if (YII_DEBUG) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.33.10'],
    ];
}


return $config;
