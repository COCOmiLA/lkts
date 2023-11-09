<?php

namespace common\models\settings;

class CodeSetting extends Setting
{
    


    public static function tableName()
    {
        return '{{%code_settings}}';
    }

    public function rules()
    {
        return [
            [['value', 'name', 'description'], 'safe']
        ];
    }
}