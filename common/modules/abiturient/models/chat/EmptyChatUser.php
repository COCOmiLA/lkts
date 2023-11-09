<?php

namespace common\modules\abiturient\models\chat;

use common\models\User;
use Yii;



class EmptyChatUser extends ChatUserBase
{
    




    public static function buildNewUser(User $user): EmptyChatUser
    {
        $chatUser = parent::buildNewUser($user);
        $chatUser->nickname = EmptyChatUser::getNickname($user);
        $chatUser->user_id = ChatBase::ID_FOR_NEW_CHAT;
        $chat = EmptyChatUser::findChatByUserId($user->id);
        if ($chat) {
            $chatUser->chat_id = $chat->id;
        }

        return $chatUser;
    }

    






    public static function getNickname(User $user): string
    {
        return '';
    }

    


    public function getNickNameForContactList(): string
    {
        return Yii::t('abiturient/chat/empty-chat-user', 'Никнейм пустого чата для блока контактов в чате абита: `Чат с модератором`');
    }

    






    private static function findChatByUserId(int $userId): ?ChatPersonToEmpty
    {
        $tnChatUser = EmptyChatUser::tableName();
        $thChatBase = ChatPersonToEmpty::tableName();

        $subQuery = ChatPersonToEmpty::find()
            ->select("{$thChatBase}.id")
            ->joinWith('chatUsers')
            ->andWhere(['!=', "{$tnChatUser}.user_id", $userId]);
        $chat = ChatPersonToEmpty::find()
            ->joinWith('chatUsers')
            ->andWhere(["{$tnChatUser}.user_id" => $userId])
            ->andWhere(['NOT IN', "{$thChatBase}.id", $subQuery])
            ->one();

        return $chat;
    }
}
