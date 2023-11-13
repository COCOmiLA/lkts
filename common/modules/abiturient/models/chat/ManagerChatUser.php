<?php

namespace common\modules\abiturient\models\chat;

use backend\models\ManagerAllowChat;
use common\models\User;
use Yii;



class ManagerChatUser extends ChatUserBase
{
    


    public static function getUserRoles(): array
    {
        return [User::ROLE_MANAGER];
    }

    






    public static function getNickname(User $user): string
    {
        $managerAllowChat = ManagerAllowChat::findOne(['manager_id' => $user->id]);
        if ($managerAllowChat && $managerAllowChat->nickname) {
            return $managerAllowChat->nickname;
        }

        return ManagerAllowChat::generateTemporaryNick($user);
    }

    


    public function getNickNameForContactList(): string
    {
        return Yii::t(
            'abiturient/chat/manager-chat-user',
            'Никнейм чата с модератором для блока контактов в чате абита: `Чат с модератором ({nick})`',
            ['nick' => $this->nickname]
        );
    }

    




    public static function buildNewUser(User $user): ManagerChatUser
    {
        $chatUser = parent::buildNewUser($user);
        $chatUser->nickname = ManagerChatUser::getNickname($user);

        return $chatUser;
    }

    




    public static function getAvailableUsersWithoutChats(User $user): array
    {
        $hasActiveChatByUserId = false;
        if (!$hasEndedChatByUserId = static::hasEndedChatByUserId($user->id)) {
            $hasActiveChatByUserId = static::hasActiveChatByUserId($user->id);
        }
        if ($hasEndedChatByUserId || $hasActiveChatByUserId) {
            return [];
        }

        return [EmptyChatUser::buildNewUser($user)];
    }

    




    public static function getAvailableUsersWithEndingChats(User $user): array
    {
        if (!static::hasEndedChatByUserId($user->id)) {
            return [];
        }

        return parent::getAvailableUsersWithEndingChats($user);
    }

    





    public static function updateUserAccount(int $managerId, ?string $nickname): void
    {
        $managerAllowChat = ManagerAllowChat::findOne(['manager_id' => $managerId]);
        if (!$managerAllowChat) {
            return;
        }

        parent::updateUserAccount($managerId, $managerAllowChat->nickname);
    }
}
