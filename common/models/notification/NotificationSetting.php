<?php

namespace common\models\notification;

use common\models\settings\Setting;
use yii\helpers\ArrayHelper;

class NotificationSetting extends Setting
{
    public const MIN_REQUEST_INTERVAL = 5;
    public const MAX_REQUEST_INTERVAL = 60;
    public const DEFAULT_REQUEST_INTERVAL = 10;
    public const PARAM_REQUEST_INTERVAL = 'request_interval';

    


    public static function tableName()
    {
        return '{{%notification_settings}}';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['value', 'name', 'description'], 'safe'],
            [
                ['value'], 
                'integer', 
                'min' => static::MIN_REQUEST_INTERVAL, 
                'max' => static::MAX_REQUEST_INTERVAL,
                'when' => function($model) {
                    return $model->name === static::PARAM_REQUEST_INTERVAL;
                },
                'whenClient' => "function(attribute, value) { return false; }"
            ]
        ]);
    }
}
