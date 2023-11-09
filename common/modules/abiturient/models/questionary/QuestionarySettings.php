<?php

namespace common\modules\abiturient\models\questionary;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;











class QuestionarySettings extends ActiveRecord
{
    private const BOOLEAN_TRUE = '1';

    
    private static $_memorizedSettings = [];

    


    public static function tableName()
    {
        return '{{%questionary_settings}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'created_at',
                    'updated_at',
                ],
                'required'
            ],
            [
                [
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [
                [
                    'name',
                    'value',
                    'description'
                ],
                'string',
                'max' => 255
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    






    public static function getSettingByName(string $name): bool
    {
        if (isset(QuestionarySettings::$_memorizedSettings[$name])) {
            return QuestionarySettings::$_memorizedSettings[$name];
        }

        $value = false;
        $setting = self::findOne(['name' => $name]);
        if (isset($setting)) {
            $value = $setting->value == QuestionarySettings::BOOLEAN_TRUE;
        }

        QuestionarySettings::$_memorizedSettings[$name] = $value;

        return QuestionarySettings::$_memorizedSettings[$name];
    }
}
