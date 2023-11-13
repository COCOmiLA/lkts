<?php

namespace common\models\settings;

class ParentDataSetting extends Setting
{
    public static function tableName(): string
    {
        return '{{%parent_data_settings}}';
    }
    
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 100],
            [['value', 'description'], 'string', 'max' => 1000],
            ['name', 'unique'],
        ];
    }
}
