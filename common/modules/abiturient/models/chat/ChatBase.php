<?php

namespace common\modules\abiturient\models\chat;

use backend\models\RBACAuthAssignment;
use common\components\DateTimeHelper;
use common\models\errors\RecordNotValid;
use common\models\notification\Notification;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

















class ChatBase extends ActiveRecord
{
    use HtmlPropsEncoder;

    public const STATUS_STARTED = 1;
    public const STATUS_ACTIVE = 2;
    public const STATUS_ENDING = 3;
    public const STATUS_OPEN_AGAIN = 4;

    public const ID_FOR_NEW_CHAT = 'new';
    public const ID_FOR_ARCHIVE_CHAT = 'archive';

    
    public $chatUserClass;

    public function init()
    {
        $this->chatUserClass = ChatUserBase::class;

        parent::init();
    }

    


    public static function tableName()
    {
        return '{{%chat}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'type',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                'integer'
            ],
            [
                'status',
                'default',
                'value' => self::STATUS_STARTED,
            ]
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        foreach ($this->getChatHistories()->all() as $dataToDelete) {
            if (!$dataToDelete->delete()) {
                $errorFrom = "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");

                return false;
            }
        }

        return true;
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getChatHistories(): ActiveQuery
    {
        return $this->hasMany(ChatHistoryBase::class, ['chat_id' => 'id']);
    }

    




    public function getChatMessages(): ActiveQuery
    {
        return $this->hasMany(ChatMessageBase::class, ['chat_id' => 'id']);
    }

    




    public function getChatFiles(): ActiveQuery
    {
        return $this->hasMany(ChatFileBase::class, ['chat_id' => 'id']);
    }

    




    public function getChatUsers(): ActiveQuery
    {
        return $this->hasMany(ChatUserBase::class, ['chat_id' => 'id']);
    }

    






    public function getChatUserByUserId(int $userId): ?ChatUserBase
    {
        return $this
            ->getChatUsers()
            ->where(['user_id' => $userId])
            ->one();
    }

    






    public function getOrCreateChatUserByUserId(int $userId): ChatUserBase
    {
        $user = $this->getChatUserByUserId($userId);
        if ($user) {
            return $user;
        }

        $userRoles = RBACAuthAssignment::getRolesByUsersIds([$userId]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            
            if (!$this->save()) {
                throw new RecordNotValid($this);
            }

            
            if (!$this->addUser((int)$userId, $userRoles)) {
                throw new ServerErrorHttpException('Не удалось создать пользователя для чата');
            }

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error("Ошибка получения пользователя: {$th->getMessage()}", 'ChatBase.getOrCreateChatUserByUserId');
            $transaction->rollBack();

            throw $th;
        }

        return $this->getChatUserByUserId($userId);
    }

    







    public static function getOrCreateChat(int $chatId, array $userIds): ChatBase
    {
        $chat = static::findOne($chatId);
        if (!$chat) {
            $chat = static::createNewChat($userIds);
        }

        return $chat;
    }

    






    public static function createNewChat(array $usersIds): ?ChatBase
    {
        $userRoles = RBACAuthAssignment::getRolesByUsersIds($usersIds);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            
            $chat = new static();
            if (!$chat->save()) {
                throw new RecordNotValid($chat);
            }

            
            if (!$chat->addUsers($usersIds, $userRoles)) {
                throw new ServerErrorHttpException('Не удалось создать пользователя для чата');
            }

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error("Ошибка создания нового чата: {$th->getMessage()}", 'ChatBase.createNewChat');
            $transaction->rollBack();
            throw $th;
        }

        return $chat;
    }

    







    public function addUsers(array $usersIds, array $userRoles): bool
    {
        foreach ($usersIds as $userId) {
            $success = $this->addUser((int)$userId, $userRoles);

            if (!$success) {
                return false;
            }
        }
        return true;
    }

    







    public function addUser(int $userId, array $userRoles): bool
    {
        $this->chatUserClass = ChatUserBase::class;
        if (key_exists($userId, $userRoles)) {
            switch ($userRoles[$userId]) {
                case User::ROLE_MANAGER:
                    $this->chatUserClass = ManagerChatUser::class;

                    break;

                case User::ROLE_ABITURIENT:
                    $this->chatUserClass = AbiturientChatUser::class;

                    break;

                default:
                    throw new ServerErrorHttpException('Не удалось определить роль пользователя чата');
            }
        }

        $user = $this->chatUserClass::buildNewUser(User::findOne($userId));
        $this->link('chatUsers', $user);

        if (!$user->save()) {
            throw new RecordNotValid($user);
        }

        return true;
    }

    







    public function addMessage(string $message, int $userId): bool
    {
        $user = $this->getOrCreateChatUserByUserId($userId);
        $message = ChatMessageBase::createNewMessage($this, $message, $user);
        $this->link('chatMessages', $message);

        if (!$message->save()) {
            throw new RecordNotValid($message);
        }

        return true;
    }

    







    public function addFiles(array $files, int $userId): bool
    {
        $user = Yii::$app->user->identity;

        $chatFilesClass = ChatFileBase::class;
        if ($user->isInRole(User::ROLE_MANAGER)) {
            $chatFilesClass = ManagerChatFile::class;
        } elseif ($user->isInRole(User::ROLE_ABITURIENT)) {
            $chatFilesClass = AbiturientChatFile::class;
        }
        $user = $this->getOrCreateChatUserByUserId($userId);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $chatFilesClass::createNewFile($this, $files, $user);

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error("Ошибка добавления файлов в чат: {$th->getMessage()}", 'ChatBase.addFiles');

            $transaction->rollBack();
            return false;
        }

        return true;
    }

    







    public function renderHistory($controller, $user = null): string
    {
        if (!$user) {
            $user = Yii::$app->user->identity;
        }

        $histories = $this->getChatHistories()
            ->with('message')
            ->with('message.author')
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
        if (!$histories) {
            return '';
        }

        $render = '';
        foreach ($histories as $history) {
            

            $render .= $history->renderEvent($controller, $user);
        }
        $this->markAllFilesAsRead($user);
        $this->markAllMessagesAsRead($user);
        $this->markAllNotificationsAsRead($user);

        return $render;
    }

    






    private function markAllNotificationsAsRead(User $user): bool
    {
        $tnNotification = Notification::tableName();
        $tnChatUser = ChatUserBase::tableName();

        $sendersIdQuery = ChatUserBase::find()
            ->select("{$tnChatUser}.user_id")
            ->andWhere(["{$tnChatUser}.chat_id" => $this->id])
            ->andWhere(['!=', "{$tnChatUser}.user_id", $user->id]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Notification::updateAll(
                [
                    'read_at' => DateTimeHelper::mstime(),
                    'updated_at' => DateTimeHelper::mstime(),
                ],
                [
                    'AND',
                    [
                        "{$tnNotification}.shown" => true,
                        "{$tnNotification}.category" => Notification::CATEGORY_CHAT,
                        "{$tnNotification}.receiver_id" => $user->id,
                    ],
                    ['IN', "{$tnNotification}.read_at", [0, null]],
                    ['IN', "{$tnNotification}.sender_id", $sendersIdQuery]
                ]
            );

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error("Ошибка пометки 'пузырей' как прочитанные: {$th->getMessage()}", 'ChatBase.markAllBlobsAsRead');

            $transaction->rollBack();
            return false;
        }

        return true;
    }

    







    private function markAllBlobsAsRead(User $user, string $blobClass): bool
    {
        $tnChatBlobBase = $blobClass::tableName();
        $tnChatUser = ChatUserBase::tableName();

        $chatUsersQuery = ChatUserBase::find()
            ->select("{$tnChatUser}.id")
            ->andWhere([
                "{$tnChatUser}.chat_id" => $this->id,
                "{$tnChatUser}.user_id" => $user->id,
            ]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $blobClass::updateAll(
                [
                    'updated_at' => time(),
                    'status' => $blobClass::STATUS_READ,
                ],
                [
                    'and',
                    [
                        "{$tnChatBlobBase}.chat_id" => $this->id,
                        "{$tnChatBlobBase}.status" => $blobClass::STATUS_NEW,
                    ],
                    ['NOT IN', "{$tnChatBlobBase}.author_id", $chatUsersQuery]
                ]
            );

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error("Ошибка пометки 'пузырей' как прочитанные: {$th->getMessage()}", 'ChatBase.markAllBlobsAsRead');

            $transaction->rollBack();
            return false;
        }

        return true;
    }

    






    private function markAllFilesAsRead(User $user): bool
    {
        return static::markAllBlobsAsRead($user, ChatFileBase::class);
    }

    






    private function markAllMessagesAsRead(User $user): bool
    {
        return static::markAllBlobsAsRead($user, ChatMessageBase::class);
    }

    




    public function statusTransition(): void
    {
        if ($this->status == ChatBase::STATUS_ENDING) {
            $this->status = ChatBase::STATUS_OPEN_AGAIN;
        }
        if ($this->status == ChatBase::STATUS_STARTED) {
            $this->status = ChatBase::STATUS_ACTIVE;
        }
    }

    




    public function getCanStartingOrOpenAgain(): bool
    {
        return !in_array($this->status, [ChatBase::STATUS_ACTIVE, ChatBase::STATUS_OPEN_AGAIN]);
    }
}
