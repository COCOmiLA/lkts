<?php

namespace backend\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\log\Logger;











class SystemLog extends \yii\db\ActiveRecord
{
    const CATEGORY_NOTIFICATION = 'notification';

    


    public static function tableName()
    {
        return '{{%system_log}}';
    }

    


    public function rules()
    {
        return [
            [['level', 'log_time', 'message'], 'integer'],
            [['log_time'], 'required'],
            [['prefix'], 'string'],
            [['category'], 'string', 'max' => 255]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'level' => Yii::t('backend', 'Уровень'),
            'category' => Yii::t('backend', 'Категория'),
            'log_time' => Yii::t('backend', 'Время события'),
            'prefix' => Yii::t('backend', 'Префикс'),
            'message' => Yii::t('backend', 'Сообщение'),
        ];
    }

    public function getLevelName()
    {
        switch ($this->level) {
            case Logger::LEVEL_ERROR:
                return Yii::t('backend', 'Ошибка');
            case Logger::LEVEL_WARNING:
                return Yii::t('backend', 'Предупреждение');
            case Logger::LEVEL_INFO:
                return Yii::t('backend', 'Информация');
            default:
                return Yii::t('backend', 'Отладка');
        }
    }

    



    public static function getCount()
    {
        static $count = null;
        if ($count === null) {
            [$count, $spend_seconds] = SystemLog::measureTime(function () {
                return SystemLog::find()->count();
            });
            if ($count > 500_000 || $spend_seconds > 3) {
                Yii::$app->session->setFlash('alert', [
                    'body' => "В вашем логе {$count} записей. Пожалуйста, " . Html::a('очистите его', Url::to(['/cleaner/index']), ['class' => 'text-primary']) . " во избежание проблем с производительностью.",
                    'options' => ['class' => 'alert-warning']
                ]);
            }
        }
        return $count;
    }

    protected static function measureTime($callback): array
    {
        $start = microtime(true);
        $result = $callback();
        $time = microtime(true) - $start;
        $spend_seconds = $time * 1000;
        return [$result, $spend_seconds];
    }
}
