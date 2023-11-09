<?php

namespace common\models\notification;

use Yii;









class NotificationType extends \yii\db\ActiveRecord
{
    const TYPE_EMAIL = 'email';
    const TYPE_POPUP = 'popup';
    
    


    public static function tableName()
    {
        return '{{%notification_type}}';
    }

    


    public function rules()
    {
        return [
            [['key'], 'required'],
            [['enabled'], 'integer'],
            [['description', 'key'], 'string', 'max' => 255],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'description' => Yii::t('common/models/notification-type', 'Подпись для поля "description" формы "Способ доставки уведомлений": `Описание`'),
            'key' => Yii::t('common/models/notification-type', 'Подпись для поля "key" формы "Способ доставки уведомлений": `Ключ`'),
            'enabled' => Yii::t('common/models/notification-type', 'Подпись для поля "enabled" формы "Способ доставки уведомлений": `Включен`'),
        ];
    }
    
    



    public static function find()
    {
        return new NotificationTypeQuery(get_called_class());
    }
}
