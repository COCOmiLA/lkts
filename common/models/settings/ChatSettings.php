<?php

namespace common\models\settings;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;











class ChatSettings extends ActiveRecord
{
    public const MIN_REQUEST_INTERVAL = 5;
    public const MAX_REQUEST_INTERVAL = 60;
    public const PARAM_REQUEST_INTERVAL = 'request_interval';
    public const ENABLE_CHAT = 'enable_chat';

    


    public static function tableName()
    {
        return '{{%chat_settings}}';
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
                'min' => ChatSettings::MIN_REQUEST_INTERVAL,
                'max' => ChatSettings::MAX_REQUEST_INTERVAL,
                'when' => function ($model) {
                    return $model->name === ChatSettings::PARAM_REQUEST_INTERVAL;
                },
                'whenClient' => "function(attribute, value) { return false; }"
            ],
            [
                ['value'],
                'required',
                'when' => function ($model) {
                    return $model->name === ChatSettings::PARAM_REQUEST_INTERVAL;
                },
                'whenClient' => "function(attribute, value) { return false; }"
            ]
        ];
    }

    


    public function attributeLabels()
    {
        return ['value' => $this->description];
    }

    




    public static function getValueByName(string $name): string
    {
        $setting = ChatSettings::findOne(['name' => $name]);
        if ($setting) {
            return $setting->value;
        }

        return '';
    }
}
