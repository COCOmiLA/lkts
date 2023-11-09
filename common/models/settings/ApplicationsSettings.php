<?php

namespace common\models\settings;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;











class ApplicationsSettings extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%applications_settings}}';
    }

    


    public function rules()
    {
        return [
            [
                ['name', 'type'],
                'string',
            ],
            [
                [
                    'description',
                    'value'
                ],
                'string',
                'max' => 1000
            ],
            [
                ['value'],
                'string',
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return ['value' => $this->description];
    }

    




    public static function getValueByName(string $name): string
    {
        
        if (ApplicationsSettings::getDb()->getTableSchema(ApplicationsSettings::tableName()) === null) {
            return '';
        }
        $setting = ApplicationsSettings::findOne(['name' => $name]);
        if ($setting) {
            return $setting->value;
        }

        return '';
    }
}
