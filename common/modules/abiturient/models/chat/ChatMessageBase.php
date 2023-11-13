<?php

namespace common\modules\abiturient\models\chat;

use common\components\behaviors\timestampBehavior\TimestampBehaviorMilliseconds;
use common\components\DateTimeHelper;
use common\components\UUIDManager;
use common\models\errors\RecordNotValid;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use Yii;
use yii\base\Controller;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use yii\web\ServerErrorHttpException;

















class ChatMessageBase extends ActiveRecord
{
    use HtmlPropsEncoder;

    public const STATUS_NEW = 1;
    public const STATUS_READ = 2;

    
    private const MAX_LENGTH_NOTIFICATION_MESSAGE = 123;

    


    public static function tableName()
    {
        return '{{%chat_message}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehaviorMilliseconds::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'chat_id',
                    'author_id',
                ],
                'required'
            ],
            [
                [
                    'status',
                    'chat_id',
                    'author_id',
                    'created_at',
                    'updated_at',
                ],
                'integer'
            ],
            [
                ['message'],
                'string'
            ],
            [
                ['status'],
                'default',
                'value' => ChatMessageBase::STATUS_NEW
            ],
            [
                ['mark_is_not_read',],
                'boolean'
            ],
            [
                ['chat_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatBase::class,
                'targetAttribute' => ['chat_id' => 'id']
            ],
            [
                ['author_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatUserBase::class,
                'targetAttribute' => ['author_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    public function afterFind()
    {
        parent::afterFind();
        if ($this->message && is_resource($this->message)) {
            $this->message = stream_get_contents($this->message);
        }
    }

    




    public function getChatHistory(): ActiveQuery
    {
        return $this->hasOne(ChatHistoryBase::class, ['message_id' => 'id']);
    }

    




    public function getChat(): ActiveQuery
    {
        return $this->hasOne(ChatBase::class, ['id' => 'chat_id']);
    }

    




    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(ChatUserBase::class, ['id' => 'author_id']);
    }

    








    public static function createNewMessage($chat, string $message, ChatUserBase $user): ChatMessageBase
    {
        $chatMessage = new ChatMessageBase();

        $chatMessage->message = $message;
        $chatMessage->chat_id = $chat->id;
        $chatMessage->author_id = $user->id;

        if (!$chatMessage->save()) {
            throw new RecordNotValid($chatMessage);
        }

        if (!ChatHistoryBase::addedNewMessage($chatMessage)) {
            throw new ServerErrorHttpException('Ошибка добавления нового сообщения');
        }

        return $chatMessage;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        foreach ($this->getChatHistory()->all() as $dataToDelete) {
            if (!$dataToDelete->delete()) {
                $errorFrom = "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");

                return false;
            }
        }

        return true;
    }

    







    public function render($controller, User $user): string
    {
        $data = [
            'time' => DateTimeHelper::dateFromMstime('d.m.Y H:i', $this->created_at),
            'nickname' => $this->author->nickname,
            'messageUid' => UUIDManager::GetUUID(),
            'messageOutput' => $this->message,
        ];

        $path = '@chatPartialView/outgoing-message-template';
        if ($this->author->user_id == $user->id) {
            $path = '@chatPartialView/incoming-message-template';
            $data['status'] = 'fa fa-check success';
        }


        return $controller->renderPartial($path, $data);
    }

    






    public static function getTotalMessagesCountQuery(ChatBase $chat): ActiveQuery
    {
        $tn = static::tableName();
        return static::find()
            ->andWhere(["{$tn}.chat_id" => $chat->id]);
    }

    






    public static function getTotalMessagesCount(ChatBase $chat): int
    {
        return static::getTotalMessagesCountQuery($chat)->count();
    }

    








    public static function getMessagesCountByUserQuery(ChatBase $chat, ChatUserBase $user): ActiveQuery
    {
        $tn = static::tableName();
        return static::getTotalMessagesCountQuery($chat)
            ->andWhere(["{$tn}.author_id" => $user->id]);
    }

    








    public static function getNotReadMessagesCountByUserQuery(ChatBase $chat, ChatUserBase $user): ActiveQuery
    {
        $tn = static::tableName();
        return static::getMessagesCountByUserQuery($chat, $user)
            ->andWhere(["{$tn}.status" => static::STATUS_NEW]);
    }

    








    public static function getNotReadMessagesCountByUser(ChatBase $chat, ChatUserBase $user): int
    {
        return static::getNotReadMessagesCountByUserQuery($chat, $user)->count();
    }

    




    public function renderForNotification(): string
    {
        $message = (string)$this->message;
        if (mb_strlen((string)$message) > static::MAX_LENGTH_NOTIFICATION_MESSAGE) {
            $message = StringHelper::truncate($message, static::MAX_LENGTH_NOTIFICATION_MESSAGE);
        }

        return $message;
    }
}
