<?php

return \yii\helpers\ArrayHelper::merge(
    require(__DIR__.'/_base.php'),
    [
        
        'format' => 'po',
        
        'messagePath' => Yii::getAlias('@common/messages'),
        
        'catalog' => 'messages',
        
        'overwrite' => true,
    ]
);
