<?php

require(__DIR__ . '/../../vendor/autoload.php');


require(__DIR__ . '/../../common/env.php');


require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');


require(__DIR__ . '/../../common/config/bootstrap.php');


$config = require(__DIR__ . '/../config/base.php');

$app = (new yii\web\Application($config));
$app->run();
