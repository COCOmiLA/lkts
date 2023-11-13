<?php

namespace common\models\notification;

use backend\models\UploadableFileTrait;
use common\components\AttachmentManager;
use common\components\behaviors\timestampBehavior\TimestampBehaviorMilliseconds;
use common\models\interfaces\FileToSendInterface;
use common\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;











class NotificationAttachment extends ActiveRecord implements FileToSendInterface
{
    use UploadableFileTrait;

    public $file;

    


    public static function tableName()
    {
        return '{{%notification_attachment}}';
    }

    


    public function rules()
    {
        return [
            [['notification_id'], 'required'],
            [['notification_id'], 'integer'],
            [['notification_id'], 'exist', 'skipOnError' => true, 'targetClass' => Notification::class, 'targetAttribute' => ['notification_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehaviorMilliseconds::class
            ]
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'notification_id' => Yii::t('common/models/notification-attachment', 'Подпись для поля "notification_id" формы "Уведомление": `Уведомление`'),
        ];
    }

    




    public function getNotification()
    {
        return $this->hasOne(Notification::class, ['id' => 'notification_id']);
    }

    public static function getFileRelationTable()
    {
        return '{{%notification_attachment_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'notification_attachment_id';
    }

    protected function getBasePathToStoreFiles()
    {
        return '@storage/web/notifications/';
    }

    protected function getOwnerId()
    {
        return $this->notification->sender->id ?? 1; 
    }

    public function getMimeType()
    {
        return AttachmentManager::GetMimeType($this->getExtension());
    }

    public function checkAccess(User $user): bool
    {
        if ($user->isModer()) {
            return true;
        } elseif ($user->id == $this->notification->receiver_id) {
            return true;
        } else {
            return false;
        }
    }
}
