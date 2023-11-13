<?php

namespace common\modules\abiturient\models\chat;

use backend\models\RBACAuthAssignment;
use common\components\LikeQueryManager;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\StringHelper;





























class ChatUserBase extends ActiveRecord
{
    use HtmlPropsEncoder;

    


    public static function tableName()
    {
        return '{{%chat_user}}';
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
                    'user_id',
                    'chat_id'
                ],
                'required'
            ],
            [
                [
                    'user_id',
                    'chat_id',
                    'avatar_id',
                    'created_at',
                    'updated_at',
                    'status',

                ],
                'integer'
            ],
            [
                'archive',
                'default',
                'value' => false
            ],
            [
                [
                    'archive',
                    'online_status',
                ],
                'boolean'
            ],
            [
                [
                    'first_name',
                    'second_name',
                    'last_name',
                    'nickname',
                    'email'
                ],
                'string',
                'max' => 255
            ],
            [
                ['chat_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatBase::class,
                'targetAttribute' => ['chat_id' => 'id']
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    


    public static function getUserRoles(): array
    {
        return [];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        foreach ($this->getChatMessages()->all() as $dataToDelete) {
            if (!$dataToDelete->delete()) {
                $errorFrom = "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");

                return false;
            }
        }
        foreach ($this->getChatFiles()->all() as $chatFile) {
            $chatFile->deleteAttachedFile();
        }

        $tnChatUsers = ChatUserBase::tableName();
        $inChatHasOtherUsers = ChatBase::find()
            ->joinWith('chatUsers')
            ->andWhere(["{$tnChatUsers}.chat_id" => $this->chat_id])
            ->andWhere(['!=', "{$tnChatUsers}.user_id", $this->user_id])
            ->exists();
        if (!$inChatHasOtherUsers) {
            foreach ($this->getChat()->all() as $dataToDelete) {
                if (!$dataToDelete->delete()) {
                    $errorFrom = "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                    Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");

                    return false;
                }
            }
        }

        return true;
    }

    




    public function getChatMessages(): ActiveQuery
    {
        return $this->hasMany(ChatMessageBase::class, ['author_id' => 'id']);
    }

    




    public function getChatFiles(): ActiveQuery
    {
        return $this->hasMany(ChatFileBase::class, ['author_id' => 'id']);
    }

    




    public function getChat(): ActiveQuery
    {
        return $this->hasOne(ChatBase::class, ['id' => 'chat_id']);
    }

    






    public static function hasActiveChatByUserId(int $userId): bool
    {
        return static::hasChatByUserIdWithStatus($userId, [ChatBase::STATUS_ACTIVE, ChatBase::STATUS_OPEN_AGAIN]);
    }

    






    public static function hasEndedChatByUserId(int $userId): bool
    {
        return static::hasChatByUserIdWithStatus($userId, [ChatBase::STATUS_ENDING]);
    }

    







    public static function hasChatByUserIdWithStatus(int $userId, array $statuses): bool
    {
        $tnChatBase = ChatBase::tableName();
        $tnThatChatUsers = 'thatChatUsers';
        $tnThisChatUser = 'thisChatUser';
        $tnUser = User::tableName();

        $subQuery = static::getAvailableUsersQuery()->select("{$tnUser}.id");
        return ChatBase::find()
            ->joinWith('chatUsers thatChatUsers')
            ->joinWith('chatUsers thisChatUser')
            ->andWhere([
                "{$tnThisChatUser}.archive" => false,
                "{$tnThisChatUser}.user_id" => $userId,
            ])
            ->andWhere(['IN', "{$tnChatBase}.status", $statuses])
            ->andWhere(['IN', "{$tnThatChatUsers}.user_id", $subQuery])
            ->exists();
    }

    




    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    





    public static function find(): ActiveQuery
    {
        $tnRbacAuthAssignment = RBACAuthAssignment::tableName();

        $query = parent::find();

        if (static::getUserRoles()) {
            $query->joinWith('user.rbacAuthAssignment')
                ->andWhere(['IN', "{$tnRbacAuthAssignment}.item_name", static::getUserRoles()]);
        }
        return $query;
    }

    




    public static function getAvailableUsersQuery(): ActiveQuery
    {
        $tnRbacAuthAssignment = RBACAuthAssignment::tableName();

        $users = User::find();

        if (static::getUserRoles()) {
            $users->joinWith('rbacAuthAssignment')
                ->andWhere(['IN', "{$tnRbacAuthAssignment}.item_name", static::getUserRoles()]);
        }
        return $users;
    }

    







    public static function getAvailableUsersWithChatsQuery(User $user): ActiveQuery
    {
        $tnChatUser = static::tableName();
        $allChats = ChatUserBase::find()
            ->select("{$tnChatUser}.chat_id")
            ->andWhere([
                "{$tnChatUser}.archive" => false,
                "{$tnChatUser}.user_id" => $user->id
            ]);

        return static::find()
            ->andWhere(["{$tnChatUser}.archive" => false])
            ->andWhere(['IN', "{$tnChatUser}.chat_id", $allChats]);
    }

    







    public static function getAvailableUsersWithChats(User $user): array
    {
        return static::getAvailableUsersWithChatsQuery($user)->all();
    }

    







    public static function getAvailableUsersWithEndingChatsQuery(User $user): ActiveQuery
    {
        $tnChatUser = static::tableName();
        $tnChatBase = ChatBase::tableName();
        $allChats = ChatUserBase::find()
            ->select("{$tnChatUser}.chat_id")
            ->joinWith('chat')
            ->andWhere([
                "{$tnChatUser}.user_id" => $user->id,
                "{$tnChatBase}.status" => ChatBase::STATUS_ENDING,
            ]);

        return static::find()
            ->andWhere(['IN', "{$tnChatUser}.chat_id", $allChats]);
    }

    







    public static function getAvailableUsersWithEndingChats(User $user): array
    {
        return static::getAvailableUsersWithEndingChatsQuery($user)->all();
    }

    







    public static function getAvailableUsersWithoutChatsQuery(User $user): ActiveQuery
    {
        $tnUser = User::tableName();
        $tnChatUser = static::tableName();

        $availableChatsIdForCurrentUser = static::getAvailableUsersWithChatsQuery($user)
            ->select("{$tnChatUser}.user_id")
            ->column();

        return static::getAvailableUsersQuery()
            ->andWhere(['!=', "{$tnUser}.id", $user->id])
            ->andWhere(['NOT IN', "{$tnUser}.id", $availableChatsIdForCurrentUser]);
    }

    






    public static function getAvailableUsersWithoutChats(User $user): array
    {
        return static::getAvailableUsersWithoutChatsQuery($user)->all();
    }

    






    public static function buildNewUser(User $user): ChatUserBase
    {
        $chatUser = new static();
        $chatUser->user_id = $user->id;

        return $chatUser;
    }

    







    public static function getOrCreateUser($chat, User $user): ChatUserBase
    {
        if (!$chat || ($chat && $chat instanceof ChatPersonToEmpty)) {
            return static::buildNewUser($user);
        }

        $tnUser = static::tableName();
        $tnChat = ChatBase::tableName();
        $chatUser = static::find()
            ->joinWith('chat')
            ->andWhere([
                'or',
                ["{$tnUser}.archive" => false],
                [
                    'and',
                    ["{$tnUser}.archive" => true],
                    ["{$tnChat}.status" => ChatBase::STATUS_ENDING]
                ],
            ])
            ->andWhere([
                "{$tnUser}.chat_id" => $chat->id,
                "{$tnUser}.user_id" => $user->id,
            ])
            ->one();

        if (!$chatUser) {
            $chatUser = static::buildNewUser($user);
            $chatUser->chat_id = $chat->id;

            if (!$chatUser->save()) {
                throw new RecordNotValid($chatUser);
            }
        }

        return $chatUser;
    }

    




    public function getOnlineStatusForHuman(): string
    {
        return $this->online_status ?
            Yii::t(
                'abiturient/chat/chat-user-base',
                'Подпись статуса "Онлайн", модели пользователя чата: `Онлайн`'
            ) :
            Yii::t(
                'abiturient/chat/chat-user-base',
                'Подпись статуса "Оффлайн", модели пользователя чата: `Оффлайн`'
            );
    }

    




    public function getOnlineStatus(): string
    {
        return $this->online_status ? 'online' : 'offline';
    }

    






    public function renderHeader($controller): string
    {
        return $controller->renderPartial(
            '@chatHeaderView',
            $this->processDataForRenderHeader()
        );
    }

    





    public function processDataForRenderHeader(): array
    {
        return [
            'nickname' => $this->nickname,
            'destinationUserId' => $this->user_id,
            'totalMessagesCount' => $this->totalMessagesCount,
        ];
    }

    




    public function getTotalMessagesCount(): int
    {
        $chat = $this->chat;
        if (!$chat) {
            return 0;
        }

        return ChatMessageBase::getTotalMessagesCount($chat) + ChatFileBase::getTotalFilesCount($chat);
    }

    





    public function getNotReadMessagesCount(): int
    {
        $chat = $this->chat;
        if (!$chat) {
            return 0;
        }

        return ChatMessageBase::getNotReadMessagesCountByUser($chat, $this) + ChatFileBase::getNotReadFilesCountByUser($chat, $this);
    }

    








    public static function getOtherUsersIds(int $chatId, int $userId): array
    {
        $chatUsers = static::find()
            ->andWhere(['archive' => false])
            ->andWhere(['chat_id' => $chatId])
            ->andWhere(['!=', 'id', $userId])
            ->all();

        $usersIds = [];
        foreach ($chatUsers as $chatUser) {
            $usersIds[] = $chatUser->user_id;
        }

        return $usersIds;
    }

    




    public function makeArchive(): bool
    {
        $this->archive = true;

        return $this->save();
    }

    




    public function makeActive(): bool
    {
        $this->archive = false;

        return $this->save();
    }

    







    public static function filteringChatUserBySearchModel(ActiveQuery $chatUserQuery, ChatSearchModel $searchModel): ActiveQuery
    {
        $tnUser = User::tableName();
        $tnChatUserBase = ChatUserBase::tableName();

        $userSubQuery = User::find()->select("{$tnUser}.id");

        
        if (!EmptyCheck::isEmpty($searchModel->email)) {
            $userSubQuery = static::filteringChatUserByEmail($userSubQuery, $searchModel->email);
        }

        
        if (!EmptyCheck::isEmpty($searchModel->full_name)) {
            $userSubQuery = static::filteringChatUserByFullName($userSubQuery, $searchModel->full_name);
        }

        
        if (!EmptyCheck::isEmpty($searchModel->applications)) {
            $userSubQuery = static::filteringChatUserByApplications($userSubQuery, $searchModel->applications);
        }

        return $chatUserQuery->andWhere(['IN', "{$tnChatUserBase}.user_id", $userSubQuery]);
    }

    







    private static function filteringChatUserByEmail(ActiveQuery $userQuery, string $email): ActiveQuery
    {
        $tnUser = User::tableName();
        return $userQuery->andWhere([LikeQueryManager::getActionName(), "{$tnUser}.email", $email]);
    }

    







    private static function filteringChatUserByFullName(ActiveQuery $userQuery, string $fullName): ActiveQuery
    {
        $tnFio = 'fio';
        $tnUser = User::tableName();

        
        $fioTable = (new Query())
            ->select([
                'user_id AS id',
                "CONCAT(lastname, ' ', firstname, ' ', middlename) AS user_fio"
            ])
            ->from('user_profile');

        return $userQuery->innerJoin(
            [$tnFio => $fioTable],
            "{$tnFio}.id = {$tnUser}.id"
        )
            ->andWhere([LikeQueryManager::getActionName(), "{$tnFio}.user_fio", $fullName]);
    }

    







    private static function filteringChatUserByApplications(ActiveQuery $userQuery, string $applications): ActiveQuery
    {
        $tnApplicationType = ApplicationType::tableName();
        if ($applications == ApplicationType::ALIAS_FOR_EMPTY_APPLICATION) {
            return $userQuery
                ->joinWith('applications.type')
                ->andWhere(['IN', "{$tnApplicationType}.id", [null, 0]]);
        } else {
            return $userQuery
                ->joinWith('applications.type')
                ->andWhere(["{$tnApplicationType}.id" => $applications]);
        }
    }

    





    public static function updateUserAccount(int $chatUserId, ?string $nickname): void
    {
        $tnChatUserBase = ChatUserBase::tableName();
        $chatUsers = ChatUserBase::find()
            ->andWhere(["{$tnChatUserBase}.user_id" => $chatUserId])
            ->all();
        if (!$chatUsers) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($chatUsers as $chatUser) {
                

                $chatUser->nickname = $nickname;
                if (!$chatUser->save()) {
                    throw new RecordNotValid($chatUser);
                }
            }

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error(
                "Ошибка обновления аккаунта модератора: {$th->getMessage()}",
                'ChatUserBase.updateUserAccount'
            );

            $transaction->rollBack();
        }
    }

    


    public function getShortNickNameForContactList(): string
    {
        $nick = $this->nickNameForContactList;

        $maxNickLen = 27; 
        $nickEndingChars = ' ...';
        return StringHelper::truncate($nick, $maxNickLen, $nickEndingChars);
    }

    


    public function getNickNameForContactList(): string
    {
        return $this->nickname;
    }
}
