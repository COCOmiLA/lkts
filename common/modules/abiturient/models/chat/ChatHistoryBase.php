<?php

namespace common\modules\abiturient\models\chat;

use common\components\behaviors\timestampBehavior\TimestampBehaviorMilliseconds;
use common\components\DateTimeHelper;
use common\components\notification\factories\NotifierFactory;
use common\models\errors\RecordNotValid;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
















class ChatHistoryBase extends ActiveRecord
{
    use HtmlPropsEncoder;

    public const EVENT_SEND_MESSAGE = 1;
    public const EVENT_ENDING_CHAT = 2;
    public const EVENT_STARTING_CHAT = 3;
    public const EVENT_STARTING_CHAT_AGAIN = 4;
    public const EVENT_MANAGER_CHANGE_CHAT = 5;
    public const EVENT_SEND_FILE = 6;

    


    public static function tableName()
    {
        return '{{%chat_history}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehaviorMilliseconds::class];
    }

    


    public function rules()
    {
        return [
            [
                ['chat_id'],
                'required'
            ],
            [
                [
                    'event',
                    'chat_id',
                    'file_id',
                    'created_at',
                    'message_id',
                    'updated_at',
                ],
                'integer'
            ],
            [
                ['chat_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatBase::class,
                'targetAttribute' => ['chat_id' => 'id']
            ],
            [
                ['message_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatMessageBase::class,
                'targetAttribute' => ['message_id' => 'id']
            ],
            [
                ['file_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatFileBase::class,
                'targetAttribute' => ['file_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getChat(): ActiveQuery
    {
        return $this->hasOne(ChatBase::class, ['id' => 'chat_id']);
    }

    




    public function getMessage(): ActiveQuery
    {
        return $this->hasOne(ChatMessageBase::class, ['id' => 'message_id']);
    }

    




    public function getFile(): ActiveQuery
    {
        $user = Yii::$app->user->identity;

        $chatFilesClass = ChatFileBase::class;
        if ($user->isInRole(User::ROLE_MANAGER)) {
            $chatFilesClass = ManagerChatFile::class;
        } elseif ($user->isInRole(User::ROLE_ABITURIENT)) {
            $chatFilesClass = AbiturientChatFile::class;
        }

        return $this->hasOne($chatFilesClass, ['id' => 'file_id']);
    }

    






    public static function addedNewMessage(ChatMessageBase $message): bool
    {
        $chatHistory = new static();
        $chatHistory->event = static::EVENT_SEND_MESSAGE;
        $chatHistory->message_id = $message->id;
        $chatHistory->chat_id = $message->chat_id;

        if (!$chatHistory->save()) {
            throw new RecordNotValid($chatHistory);
        }

        return static::sendNotification($message);
    }

    






    public static function addedNewFile(ChatFileBase $file): bool
    {
        $chatHistory = new static();
        $chatHistory->event = static::EVENT_SEND_FILE;
        $chatHistory->file_id = $file->id;
        $chatHistory->chat_id = $file->chat_id;

        if (!$chatHistory->save()) {
            throw new RecordNotValid($chatHistory);
        }

        return static::sendNotification($file);
    }

    







    public function renderEvent(Controller $controller, User $user): string
    {
        switch ($this->event) {
            case ChatHistoryBase::EVENT_SEND_MESSAGE:
                return $this->message->render($controller, $user);

            case ChatHistoryBase::EVENT_SEND_FILE:
                return $this->file->render($controller, $user);

            case ChatHistoryBase::EVENT_ENDING_CHAT:
            case ChatHistoryBase::EVENT_STARTING_CHAT:
                $message = '';
                if ($this->event === ChatHistoryBase::EVENT_ENDING_CHAT) {
                    $message = Yii::t('abiturient/chat-history-base/all', 'Сообщение о том что модератор завершил чат; в окне истории чата: `Модератор завершил заявку`');
                } elseif ($this->event === ChatHistoryBase::EVENT_STARTING_CHAT) {
                    $message = Yii::t('abiturient/chat-history-base/all', 'Сообщение о том что модератор начал чат; в окне истории чата: `Модератор принял заявку в обработку`');
                }

                $data = [
                    'time' => DateTimeHelper::dateFromMstime('d.m.Y H:i', $this->created_at),
                    'message' => $message
                ];

                $path = '@chatPartialView/history-separator';
                return $controller->renderPartial($path, $data);

            case ChatHistoryBase::EVENT_STARTING_CHAT_AGAIN:
                $data = [
                    'time' => DateTimeHelper::dateFromMstime('d.m.Y H:i', $this->created_at),
                    'message' => Yii::t(
                        'abiturient/chat-history-base/all',
                        'Сообщение о том что поступающий заново открыл чат; в окне истории чата: `Поступающий повторно открыл заявку`'
                    ),
                ];

            case ChatHistoryBase::EVENT_MANAGER_CHANGE_CHAT:
                $data = [
                    'time' => DateTimeHelper::dateFromMstime('d.m.Y H:i', $this->created_at),
                    'message' => Yii::t(
                        'abiturient/chat-history-base/all',
                        'Сообщение о том что модератор переадресовал чат; в окне истории чата: `Заявка была переадресована`'
                    ),
                ];

                $path = '@chatPartialView/history-separator';
                return $controller->renderPartial($path, $data);
        }

        return '';
    }

    






    public static function sendNotification($chatBlob): bool
    {
        $title = Yii::t(
            'abiturient/chat-history-base/all',
            'Текст уведомления о новом сообщении в чате: `Пользователь {user} отправил вам сообщение.`',
            ['user' => ArrayHelper::getValue($chatBlob, 'author.nickname')]
        );
        $body = $chatBlob->renderForNotification();
        $otherUsersIds = ChatUserBase::getOtherUsersIds($chatBlob->chat_id, $chatBlob->author_id);

        $sendMessagesId = [];
        $sendId = ArrayHelper::getValue($chatBlob, 'author.user_id');
        try {
            $sendMessagesId = NotifierFactory::getChatNotifier($sendId)->send(
                $title,
                $body,
                $otherUsersIds,
            );
        } catch (Throwable $th) {
            Yii::error(
                "Отправки уведомлений: {$th->getMessage()}",
                'ChatHistoryBase.sendNotification'
            );

            return false;
        }

        if (count($sendMessagesId) != count($otherUsersIds)) {
            $sendMessagesCount = count($sendMessagesId);
            $otherUsersMessagesCount = count($otherUsersIds);
            Yii::error(
                "Не соответствует кол-во отправленных уведомлений ({$sendMessagesCount}) с кол-вом уведомлений на отправку ({$otherUsersMessagesCount})",
                'ChatHistoryBase.sendNotification'
            );

            return false;
        }

        return true;
    }

    






    public static function managerEndingChat(ChatBase $chat): void
    {
        static::createEventOnStatusChange($chat, static::EVENT_ENDING_CHAT);
    }

    






    public static function managerRedirectChat(ChatBase $chat): void
    {
        static::createEventOnStatusChange($chat, static::EVENT_MANAGER_CHANGE_CHAT);
    }

    






    public static function abitOpenAgainChat(ChatBase $chat): void
    {
        $oldStatus = $chat->getOldAttribute('status');

        if (
            
            $oldStatus != $chat->status &&
            
            $chat->status == ChatBase::STATUS_OPEN_AGAIN
        ) {
            static::createEventOnStatusChange($chat, static::EVENT_STARTING_CHAT_AGAIN);
        }
    }

    






    public static function managerStartingChat(ChatBase $chat): void
    {
        $oldStatus = $chat->getOldAttribute('status');

        if (
            
            $oldStatus != $chat->status &&
            
            $chat->status == ChatBase::STATUS_ACTIVE
        ) {
            static::createEventOnStatusChange($chat, static::EVENT_STARTING_CHAT);
        }
    }


    







    public static function createEventOnStatusChange(ChatBase $chat, int $event): void
    {
        $chatHistory = new static();
        $chatHistory->event = $event;
        $chatHistory->chat_id = $chat->id;

        if (!$chatHistory->save()) {
            throw new RecordNotValid($chatHistory);
        }
    }
}
