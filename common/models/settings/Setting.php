<?php

namespace common\models\settings;

class Setting extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            ['name', 'string', 'max' => 100],
            ['value', 'string', 'max' => 1000],
            ['name', 'unique'],
        ]; 
    }

    


    public function attributeLabels()
    {
        return [
            'name' => 'Наименование',
            'value' => 'Значение',
        ];
    }
}