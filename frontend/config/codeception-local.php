<?php

return yii\helpers\ArrayHelper::merge(
    require dirname(dirname(__DIR__)) . '/common/config/codeception-local.php',
    require __DIR__ . '/base.php',
    require __DIR__ . '/web.php',
    require __DIR__ . '/test.php',
);
