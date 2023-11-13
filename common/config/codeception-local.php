<?php

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/base.php'),
    require(__DIR__ . '/../../common/config/web.php'),
    require(__DIR__ . '/../../common/config/test.php'),
    [
        'components' => [
            'request' => [
                
                'cookieValidationKey' => '',
                'parsers' => [
                    'application/json' => 'yii\web\JsonParser',
                ]
            ],
        ],
    ]
);
