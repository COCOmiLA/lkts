<?php

namespace common\components\notification;

use common\models\notification\NotificationSetting;

class NotificationSettingsManager
{
    public static function isWidgetEnabled(): bool
    {
        $model = NotificationSetting::findOne(['name' => 'enable_widget']);
        return isset($model) ? $model->value == 1 : false;
    }
}
