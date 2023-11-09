<?php

namespace common\modules\abiturient\models\chat;

use backend\models\RBACAuthAssignment;
use common\models\User;
use yii\db\ActiveQuery;



class ChatPersonToEmpty extends ChatBase
{
    







    public static function getOrCreateChat(int $chatId, $userId): ChatPersonToEmpty
    {
        $chat = static::findChatByUserId($chatId, $userId);
        if (!$chat) {
            $chat = static::createNewChat([$userId]);
        }

        return $chat;
    }

    







    public static function findChatByUserIdQuery(int $chatId, int $userId): ActiveQuery
    {
        $tnChatUser = ChatUserBase::tableName();
        $thChatBase = ChatBase::tableName();

        $subQuery = static::find()
            ->select("{$thChatBase}.id")
            ->joinWith('chatUsers')
            ->andWhere([
                "{$thChatBase}.id" => $chatId,
                "{$tnChatUser}.archive" => false,
            ])
            ->andWhere(['!=', "{$tnChatUser}.user_id", $userId]);
        return static::find()
            ->joinWith('chatUsers')
            ->andWhere([
                "{$thChatBase}.id" => $chatId,
                "{$tnChatUser}.user_id" => $userId,
                "{$tnChatUser}.archive" => false,
            ])
            ->andWhere(['NOT IN', "{$thChatBase}.id", $subQuery]);
    }

    







    public static function findChatByUserId(int $chatId, int $userId): ?ChatPersonToEmpty
    {
        return static::findChatByUserIdQuery($chatId, $userId)->one();
    }

    







    public static function isExists(int $chatId, array $userIds): bool
    {
        $userIdsWithAbiturientRole = ChatPersonToEmpty::getUserIdsWithAbiturientRole($userIds);

        return static::findChatByUserIdQuery($chatId, $userIdsWithAbiturientRole)->exists();
    }

    






    public static function getUserIdsWithAbiturientRole(array $userIds): int
    {
        return (int) RBACAuthAssignment::find()
            ->select('user_id')
            ->andWhere(['IN', 'user_id', $userIds])
            ->andWhere(['item_name' => User::ROLE_ABITURIENT])
            ->scalar();
    }
}
