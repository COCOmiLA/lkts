<?php

return \yii\helpers\ArrayHelper::merge(
    require(__DIR__.'/_base.php'),
    [
        
        'format' => 'db',
        
        'db' => 'db',
        
        'sourceMessageTable' => '{{%i18n_source_message}}',
        
        'messageTable' => '{{%i18n_message}}',
    ]
);
