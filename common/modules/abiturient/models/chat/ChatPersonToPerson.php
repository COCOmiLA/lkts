<?php

namespace common\modules\abiturient\models\chat;

use common\models\errors\RecordNotValid;
use common\models\User;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\web\ServerErrorHttpException;



class ChatPersonToPerson extends ChatBase
{
    


    public static function getOrCreateChat(int $chatId, $userIds): ChatBase
    {
        if (count($userIds) != 2) {
            throw new ServerErrorHttpException('Количество активных пользователей чата должно быть строго два');
        }
        $chat = static::findChatByIdAndUsersId($chatId, $userIds);
        if (!$chat) {
            $chat = static::createNewChat($userIds);
        }

        if (
            $chat instanceof ChatPersonToPerson &&
            $chat->canStartingOrOpenAgain
        ) {
            $chat->statusTransition();
            ChatHistoryBase::managerStartingChat($chat);
            ChatHistoryBase::abitOpenAgainChat($chat);

            if (!$chat->save()) {
                throw new RecordNotValid($chat);
            }

            if ($chat->status == static::STATUS_OPEN_AGAIN) {
                $chat->connectWithLastPerson();
            }
        }

        return $chat;
    }

    







    public static function findChatByIdAndUsersId(int $chatId, array $userIds): ?ChatBase
    {
        $tnFirstChatUser = 'firstChatUser';
        $tnSecondChatUser = 'secondChatUser';
        $thChatBase = ChatBase::tableName();

        [
            $secondChatUserId,
            $firstChatUserId,
        ] = $userIds;

        if ($secondChatUserId == $firstChatUserId) {
            return null;
        }
        if ($firstChatUserId === static::ID_FOR_NEW_CHAT || $secondChatUserId === static::ID_FOR_NEW_CHAT) {
            
            
            $realId = $firstChatUserId === static::ID_FOR_NEW_CHAT ? $secondChatUserId : $firstChatUserId;
            
            
            

            
            $chat = ChatPersonToPerson::findChatByOnlyAbiturientId($chatId, $realId);

            if (!$chat) {
                
                return ChatPersonToEmpty::getOrCreateChat($chatId, $realId);
            }

            return $chat;
        }

        $chat = static::find()
            ->joinWith("chatUsers {$tnFirstChatUser}")
            ->joinWith("chatUsers {$tnSecondChatUser}")
            ->andWhere([
                "{$thChatBase}.id" => $chatId,
                "{$tnFirstChatUser}.user_id" => $firstChatUserId,
                "{$tnSecondChatUser}.user_id" => $secondChatUserId,
                "{$tnFirstChatUser}.archive" => false,
                "{$tnSecondChatUser}.archive" => false,
            ])
            ->one();

        if (!$chat && ChatPersonToEmpty::isExists($chatId, $userIds)) {
            $chat = ChatPersonToPerson::find()
                ->andWhere(["{$thChatBase}.id" => $chatId])
                ->one();
        }

        return $chat;
    }

    






    public static function findChatUsersId(array $userIds): ?ChatPersonToPerson
    {
        $tnFirstChatUser = 'firstChatUser';
        $tnSecondChatUser = 'secondChatUser';

        [
            $secondChatUserId,
            $firstChatUserId,
        ] = $userIds;

        if ($secondChatUserId == $firstChatUserId) {
            return null;
        }

        $chat = static::find()
            ->joinWith("chatUsers {$tnFirstChatUser}")
            ->joinWith("chatUsers {$tnSecondChatUser}")
            ->andWhere([
                "{$tnFirstChatUser}.user_id" => $firstChatUserId,
                "{$tnSecondChatUser}.user_id" => $secondChatUserId,
                "{$tnFirstChatUser}.archive" => false,
                "{$tnSecondChatUser}.archive" => false,
            ])
            ->one();

        return $chat;
    }

    






    public function endChat(int $chatUserId): bool
    {
        $chatUser = $this->getChatUsers()
            ->andWhere(['user_id' => $chatUserId])
            ->one();
        if ($chatUser) {
            if (!$chatUser->makeArchive()) {
                throw new ServerErrorHttpException('Возникла ошибка архивирования пользователя чата');
            }
            
        }

        ChatHistoryBase::managerEndingChat($this);
        $this->status = static::STATUS_ENDING;

        if (!$this->save()) {
            throw new RecordNotValid($this);
        }

        return true;
    }

    





    public function connectWithLastPerson(): bool
    {
        $thisUserId = Yii::$app->user->identity->id;
        $tnChatUserBase = ChatUserBase::tableName();

        $chatUser = $this->getChatUsers()
            ->andWhere(['!=', "{$tnChatUserBase}.user_id", $thisUserId])
            ->orderBy(["{$tnChatUserBase}.updated_at" => SORT_DESC])
            ->one();
        if ($chatUser) {
            if (!$chatUser->makeActive()) {
                throw new ServerErrorHttpException('Возникла ошибка активирования пользователя чата');
            }
            
        }

        return true;
    }

    







    public function redirectChat(User $thisUser, User $otherManager): bool
    {
        $chatUserToArchive = $this->getChatUsers()
            ->andWhere([
                'archive' => false,
                'user_id' => $thisUser->id,
            ])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();
        if ($chatUserToArchive) {
            $chatUserToConnect = $this->getOrCreateChatUserByUserId($otherManager->id);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$chatUserToArchive->makeArchive() || !$chatUserToConnect->makeActive()) {
                    throw new ServerErrorHttpException('Не удалось произвести переадресацию чата, на другого пользователя');
                }

                $transaction->commit();
            } catch (Throwable $th) {
                $transaction->rollBack();
                Yii::error("Возникла ошибка при перенаправлении чата: {$th->getMessage()}", 'ChatPersonToPerson.redirectChat');

                return false;
            }

            ChatHistoryBase::managerRedirectChat($this);

            return true;
        }

        throw new ServerErrorHttpException('Для данного чат не найдена ни одного пользователя');
    }

    







    public static function findChatByOnlyAbiturientIdQuery(int $chatId, int $userId): ActiveQuery
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
            ->andWhere(['IN', "{$thChatBase}.id", $subQuery]);
    }

    







    public static function findChatByOnlyAbiturientId(int $chatId, int $userId): ?ChatPersonToEmpty
    {
        return static::findChatByOnlyAbiturientIdQuery($chatId, $userId)->one();
    }
}
