<?php

use common\components\secureUrlManager\SecureUrlManager;

return [
    'class' => SecureUrlManager::class,
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [

        
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/article', 'only' => ['index', 'view', 'options']],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/v1/user',
            'only' => ['index', 'view', 'options', 'current'],
            'extraPatterns' => [
                'current/<accessToken>' => 'current',
            ],
        ],

        
        '<alias:authorize>' => 'site/<alias>',

        '<alias:download>' => 'site/<alias>',
        '<alias:download-pdf-file>' => 'site/<alias>',
        '<alias:deletefile>' => 'site/<alias>',
        '<alias:settings>' => 'settings/sandbox',

        'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
        'admission/<action:\w+>' => 'admission/admission/<action>',
    ]
];
