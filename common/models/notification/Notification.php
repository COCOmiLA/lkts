<?php

namespace common\models\notification;

use backend\models\ManagerAllowChat;
use common\components\behaviors\timestampBehavior\TimestampBehaviorMilliseconds;
use common\components\DateTimeHelper;
use common\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
























class Notification extends ActiveRecord
{
    public const CATEGORY_COMMON = 'common';
    public const CATEGORY_CHAT = 'chat';
    
    


    public static function tableName()
    {
        return '{{%notification}}';
    }

    


    public function rules()
    {
        return [
            [['receiver_id', 'notification_content_id'], 'required'],
            [['sender_id', 'receiver_id', 'notification_content_id', 'read_at', 'created_at', 'updated_at'], 'integer'],
            [['category'], 'string'],
            [['notification_content_id'], 'exist', 'skipOnError' => true, 'targetClass' => NotificationContent::class, 'targetAttribute' => ['notification_content_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['receiver_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehaviorMilliseconds::class,
            ]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'sender_id' => Yii::t('common/models/notification', 'Подпись для поля "sender_id" формы "Уведомление": `Отправитель`'),
            'receiver_id' => Yii::t('common/models/notification', 'Подпись для поля "receiver_id" формы "Уведомление": `Получатель`'),
            'notification_content_id' => Yii::t('common/models/notification', 'Подпись для поля "notification_content_id" формы "Уведомление": `Контент`'),
            'category' => Yii::t('common/models/notification', 'Подпись для поля "category" формы "Уведомление": `Категория`'),
            'read_at' => Yii::t('common/models/notification', 'Подпись для поля "read_at" формы "Уведомление": `Прочитано`'),
            'created_at' => Yii::t('common/models/notification', 'Подпись для поля " created_at" формы "Уведомление": `Создано`'),
            'updated_at' => Yii::t('common/models/notification', 'Подпись для поля " updated_at" формы "Уведомление": `Обновлено`'),
        ];
    }

    




    public function getNotificationContent()
    {
        return $this->hasOne(NotificationContent::class, ['id' => 'notification_content_id']);
    }

    




    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'receiver_id']);
    }

    




    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }
    
    public function getChatManager(): ActiveQuery
    {
        return $this->hasOne(ManagerAllowChat::class, ['manager_id' => 'sender_id']);
    }

    




    public function getNotificationAttachments()
    {
        return $this->hasMany(NotificationAttachment::class, ['notification_id' => 'id']);
    }
    
    public function getTitle(): string
    {
        return $this->notificationContent->title ?? '';
    }
    
    public function getBody(): string
    {
        return $this->notificationContent->body ?? '';
    }
    
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
    
    public function markAsRead(): bool
    {
        if (!$this->isUnread()) {
            return false;
        }
        
        $this->read_at = DateTimeHelper::mstime();
        return $this->save(true, ['read_at']);
    }
    
    public function getPopupTitle(): string
    {
        if ($this->category === static::CATEGORY_CHAT) {
            if ($this->chatManager) {
                $nickname = $this->chatManager->nickname;
            } elseif ($this->sender) {
                $nickname = ManagerAllowChat::generateTemporaryNick($this->sender);
            } else {
                $nickname = 'user';
            }
            
            return \Yii::t(
                'abiturient/chat-history-base/all',
                'Текст уведомления о новом сообщении в чате: `Пользователь {user} отправил вам сообщение.`',
                ['user' => $nickname]
            );
        }
        
        return $this->title;
    }
}
