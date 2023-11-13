<?php
namespace common\components\ManagerInforming;


use yii\helpers\ArrayHelper;

class ManagerInforming
{
    private const MESSAGES=[
        'system_manager' => [
            'manager_is_not_allowed' => 'Включена опция "Рабочее место модератора ПК", интерфейс модератора портала - отключен.'
        ]
    ];

    




    public static function getMessage(string $path) {
        return ArrayHelper::getValue(self::MESSAGES, $path) ?? 'Unknown message';
    }
}