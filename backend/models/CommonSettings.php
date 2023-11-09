<?php

namespace backend\models;

use Yii;




class CommonSettings extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            ['show_technical_info_on_error', 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'show_technical_info_on_error' => Yii::t('settings/main', 'Показывать подробную техническую информацию об ошибках'),
        ];
    }

    


    public static function tableName()
    {
        return '{{%common_settings}}';
    }

    public static function getInstance(): CommonSettings
    {
        $record = CommonSettings::find()->limit(1)->one();
        if (!$record) {
            $record = new CommonSettings();
            $record->loadDefaultValues();
        }
        return $record;
    }
}