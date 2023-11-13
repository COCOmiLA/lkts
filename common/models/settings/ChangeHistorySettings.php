<?php

namespace common\models\settings;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;











class ChangeHistorySettings extends ActiveRecord
{
    public const PARAM_REQUEST_UNSIGNED_INT = [
        'first_load_limit',
        'following_load_limit',
    ];
    public const ENABLE_CHAT = 'enable_chat';

    


    public static function tableName()
    {
        return '{{%change_history_settings}}';
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
                    'updated_at'
                ],
                'integer'
            ],
            [
                ['name'],
                'string',
                'max' => 100
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
                'number',
                'min' => 0,
                'when' => function ($model) {
                    return in_array($model->name, ChangeHistorySettings::PARAM_REQUEST_UNSIGNED_INT);
                },
                'whenClient' => "function(attribute, value) { return false; }"
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return ['value' => $this->description];
    }

    




    public static function getValueByName(string $name): string
    {
        $setting = ChangeHistorySettings::findOne(['name' => $name]);
        if ($setting) {
            return $setting->value;
        }

        return '';
    }
}
