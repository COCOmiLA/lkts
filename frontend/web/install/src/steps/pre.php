<?php

$dir = __DIR__;


require($dir . '/../../../../../vendor/autoload.php');


$dotenv = new \Dotenv\Dotenv($dir . '/../../../../..');
$dotenv->load();

defined('YII_DEBUG') || define('YII_DEBUG', getenv('YII_DEBUG') === 'true');
defined('YII_ENV') || define('YII_ENV', getenv('YII_ENV') ?: 'prod');


putenv("ENABLE_MIGRATIONS_CHECK=false");


if (!class_exists('Yii')) {
    require($dir . '/../../../../../vendor/yiisoft/yii2/Yii.php');

    
    require($dir . '/../../../../../common/config/bootstrap.php');
    require($dir . '/../../../../config/bootstrap.php');
}

$config = \yii\helpers\ArrayHelper::merge(
    require($dir . '/../../../../../common/config/base.php'),
    require($dir . '/../../../../../common/config/web.php'),
    require($dir . '/../../../../config/base.php'),
    require($dir . '/../../../../config/web.php'),
    [
        'components' => array(
            'request' => array(
                'enableCsrfValidation' => false,
            ),
        ),
    ]
);
try {
    $app = (new yii\web\Application($config));
    $app->run();
} catch (\Throwable $e) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo $e->getMessage();
    die();
}
