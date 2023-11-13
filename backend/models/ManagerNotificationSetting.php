<?php

namespace backend\models;

class ManagerNotificationSetting extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%manager_notification_settings}}';
    }

    public function rules()
    {
        return [
            [['manager_id', 'name'], 'required'],
            [['manager_id'], 'integer'],
            [['name'], 'string'],
            [['value'], 'safe'],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\User::class, 'targetAttribute' => ['manager_id' => 'id']],
        ];
    }
}