<?php

namespace common\modules\abiturient\models\chat;

use common\models\User;
use Yii;



class EndedChatUser extends ChatUserBase
{
    




    public static function buildNewUser(User $user): EndedChatUser
    {
        $chatUser = parent::buildNewUser($user);
        $chatUser->nickname = EndedChatUser::getNickname($user);
        $chatUser->user_id = 'archive';
        $chat = EndedChatUser::findChatByUserId($user->id);
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
        return Yii::t('abiturient/chat/ended-chat-user', 'Никнейм закрещённого чата для блока контактов в чате абита: `Чат с модератором`');
    }

    






    private static function findChatByUserId(int $userId): ?ChatPersonToEmpty
    {
        $tnChatUser = EndedChatUser::tableName();
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
