<?php

if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}


require(__DIR__ . '/../../tests/bootstrap.php');


defined('YII_DEBUG') || define('YII_DEBUG', false);
defined('YII_ENV') || define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') || define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)));


require(__DIR__ . '/../../common/env.php');


require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');


require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = require(__DIR__ . '/../../tests/codeception/config/backend/acceptance.php');

$app = (new yii\web\Application($config));
$app->run();
