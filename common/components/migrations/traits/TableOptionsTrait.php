<?php

namespace common\components\migrations\traits;

use Yii;

trait TableOptionsTrait
{
    


    public static function GetTableOptions()
    {
        $options = null;
        if (Yii::$app->db->driverName === 'mysql') {
            $options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        return $options;
    }
}
