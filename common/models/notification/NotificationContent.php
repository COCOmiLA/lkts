<?php

namespace common\models\notification;

use common\models\traits\HtmlPropsEncoder;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;










class NotificationContent extends ActiveRecord
{
    use HtmlPropsEncoder;
    
    


    public static function tableName()
    {
        return '{{%notification_content}}';
    }

    


    public function rules()
    {
        return [
            [['body'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'title' => Yii::t('common/models/notification-content', 'Подпись для поля "title" формы "Содержимое уведомления": `Заголовок`'),
            'body' => Yii::t('common/models/notification-content', 'Подпись для поля "body" формы "Содержимое уведомления": `Тело`'),
        ];
    }

    




    public function getNotifications()
    {
        return $this->hasMany(Notification::class, ['notification_content_id' => 'id']);
    }
}
