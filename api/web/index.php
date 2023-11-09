<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');



require(__DIR__ . '/../../vendor/autoload.php');


require(__DIR__ . '/../../common/env.php');


require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');


require(__DIR__ . '/../../common/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/base.php'),
    require(__DIR__ . '/../config/main.php')
);

$app = (new yii\web\Application($config));
$app->run();