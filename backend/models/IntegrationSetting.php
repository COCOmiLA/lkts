<?php

namespace backend\models;




class IntegrationSetting extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%integration_settings}}';
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 255],
            ['value', 'string'],
        ];
    }
}