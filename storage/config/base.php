<?php




use common\components\secureUrlManager\SecureUrlManager;

return [
    'id' => 'storage',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'glide/index',
    'controllerMap' => [
        'glide' => '\trntv\glide\controllers\GlideController'
    ],
    'components' => [
        'urlManager' => require(__DIR__ . '/_urlManager.php'),
        'glide' => [
            'class' => 'trntv\glide\components\Glide',
            'sourcePath' => '@storage/web/source',
            'cachePath' => '@storage/cache',
            'maxImageSize' => getenv('GLIDE_MAX_IMAGE_SIZE'),
            'signKey' => getenv('GLIDE_SIGN_KEY')
        ],
        'request' => [
            'class' => \common\components\Request::class,
            'baseUrl' => '/storage',
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => SecureUrlManager::isHttpsEnabled()
            ],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
    ]
];
