<?php

namespace common\models\settings;

use yii\helpers\ArrayHelper;






class AuthSetting extends Setting
{
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['value'], 'number', 'min' => 0, 'when' => function() {
                    return $this->name === 'identity_cookie_duration';
                }
            ]
        ]);
    }
    
    


    public static function tableName()
    {
        return '{{%auth_settings}}';
    }
}