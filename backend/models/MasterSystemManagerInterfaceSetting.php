<?php

namespace backend\models;









class MasterSystemManagerInterfaceSetting extends \yii\db\ActiveRecord
{

    private const settingLabels = [
        'use_master_system_manager_interface'=> 'Рабочее место модератора ПК (Данная настройка отключит интерфейс модератора в портале)'
    ];

    


    public static function tableName()
    {
        return '{{%master_system_manager_interface_setting}}';
    }

    


    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 500],
            [['value'], 'string', 'max' => 6000],
            [['type'], 'string', 'max' => 250],
            [['name', 'type', 'value'], 'required']
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
            'type' => 'Type',
        ];
    }

    



    public static function GetSettingLabel($settingName) {
        return self::settingLabels[$settingName] ?? "Неизвестная настройка";
    }
}
