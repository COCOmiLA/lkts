<?php

return \yii\helpers\ArrayHelper::merge(
    require(__DIR__.'/_base.php'),
    [
        
        'format' => 'php',
        
        'messagePath' => Yii::getAlias('@common/messages'),
        
        'overwrite' => true,
    ]
);
