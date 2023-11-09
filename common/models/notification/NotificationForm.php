<?php

namespace common\models\notification;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class NotificationForm extends Model
{
    public const MAX_FILES = 20;
    
    
    public $title;
    
    
    public $body;
    
    
    public $attachments = [];
    
    
    public $types = [];
    
    
    public $receivers = [];

    public function attributeLabels(): array
    {
        return [
            'title' => Yii::t('common/models/notification-form', 'Подпись для поля "title" формы "Форма уведомления": `Заголовок`'),
            'body' => Yii::t('common/models/notification-form', 'Подпись для поля "body" формы "Форма уведомления": `Сообщение`'),
            'types' => Yii::t('common/models/notification-form', 'Подпись для поля "body" формы "Форма уведомления": `Способы доставки`'),
            'receivers' => Yii::t('common/models/notification-form', 'Подпись для поля "body" формы "Форма уведомления": `Получатели`'),
        ];
    }
    
    public function rules(): array
    {
        return [
            [['title', 'body'], 'string'],
            [['title', 'body'], 'required'],
            [['attachments'], 'file', 'maxFiles' => static::MAX_FILES],
            [['types', 'receivers'], 'required']
        ];
    }
}
