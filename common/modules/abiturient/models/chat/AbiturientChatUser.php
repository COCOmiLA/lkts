<?php

namespace common\modules\abiturient\models\chat;

use backend\models\ManagerAllowChat;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\PersonalData;
use Yii;
use yii\bootstrap4\Html;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;



class AbiturientChatUser extends ChatUserBase
{
    


    public static function getUserRoles(): array
    {
        return [User::ROLE_ABITURIENT];
    }

    






    public static function getNickname(User $user): string
    {
        return $user->fullName;
    }

    




    public static function buildNewUser(User $user): AbiturientChatUser
    {
        $chatUser = parent::buildNewUser($user);
        $chatUser->nickname = AbiturientChatUser::getNickname($user);

        return $chatUser;
    }

    




    public static function getAvailableAbitsWithoutChatsQuery(User $user): ActiveQuery
    {
        $tnUser = User::tableName();
        $tnChatFiles = ChatFileBase::tableName();
        $tnChatUser = AbiturientChatUser::tableName();
        $tnChatMessages = ChatMessageBase::tableName();

        $subQuery = parent::getAvailableUsersWithoutChatsQuery($user)
            ->select("{$tnUser}.id");

        return AbiturientChatUser::find()
            ->joinWith('chatFiles')
            ->joinWith('chatMessages')
            ->andWhere(['IN', "{$tnChatUser}.user_id", $subQuery])
            ->andWhere([
                'OR',
                ['NOT IN', "{$tnChatFiles}.id", [0, null]],
                ['NOT IN', "{$tnChatMessages}.id", [0, null]],
            ]);
    }

    




    public static function getAvailableUsersWithoutChatsQuery(User $user): ActiveQuery
    {
        $tnChat = ChatBase::tableName();

        return static::getAvailableAbitsWithoutChatsQuery($user)
            ->joinWith('chat')
            ->andWhere([
                'NOT IN',
                "{$tnChat}.status",
                [ChatBase::STATUS_ENDING, ChatBase::STATUS_ACTIVE, ChatBase::STATUS_OPEN_AGAIN]
            ]);
    }

    




    public static function getAvailableUsersWithoutChats(User $user): array
    {
        return static::getAvailableUsersWithoutChatsQuery($user)->all();
    }

    




    public static function getAvailableUsersWithEndingChatsQuery(User $user): ActiveQuery
    {
        $tnChat = ChatBase::tableName();

        return static::getAvailableAbitsWithoutChatsQuery($user)
            ->joinWith('chat')
            ->andWhere(["{$tnChat}.status" => ChatBase::STATUS_ENDING]);
    }

    




    public static function getAvailableUsersWithEndingChats(User $user): array
    {
        return static::getAvailableUsersWithEndingChatsQuery($user)->all();
    }

    




    public static function getSearchModel(User $user): ChatSearchModel
    {
        return new ChatSearchModel();
    }

    




    public static function getOtherManagers(): array
    {
        $tnUser = User::tableName();
        $tnManagerAllowChat = ManagerAllowChat::tableName();

        return User::find()
            ->leftJoin($tnManagerAllowChat, "{$tnManagerAllowChat}.manager_id = {$tnUser}.id")
            ->andWhere(['!=', "{$tnUser}.id", Yii::$app->user->id])
            ->andWhere(['NOT IN', "{$tnManagerAllowChat}.id", [0, null]])
            ->all();
    }

    


    public function processDataForRenderHeader(): array
    {
        $parentList = parent::processDataForRenderHeader();
        $destinationUserId = (int) $parentList['destinationUserId'];

        $avatarLink = AbiturientChatUser::getAvatarLink($destinationUserId);
        $otherManagerLinks = AbiturientChatUser::buildOtherManagersList($destinationUserId);
        $additionalUserInfoList = AbiturientChatUser::buildAdditionalUserInfoList($destinationUserId);

        return array_merge(
            $parentList,
            [
                'avatarLink' => $avatarLink,
                'otherManagers' => $otherManagerLinks,
                'additionalUserInfoList' => $additionalUserInfoList,
            ]
        );
    }

    






    public static function getAvatarLink(int $destinationUserId): ?string
    {
        return ArrayHelper::getValue(
            User::findOne(['id' => $destinationUserId]),
            'abiturientQuestionary.abiturientAvatar.fileDownloadUrl'
        );
    }

    






    private static function buildOtherManagersList(int $destinationUserId): array
    {
        $otherManagerLinks = [];
        $otherManager = AbiturientChatUser::getOtherManagers();

        foreach ($otherManager as $user) {
            
            $otherManagerLinks[] = [
                'label' => ManagerChatUser::buildNewUser($user)->nickname,
                'url' => [
                    '/manager-chat/redirect-chat',
                    'destination_id' => $destinationUserId,
                    'other_manager_id' => $user->id,
                ],
            ];
        }

        return $otherManagerLinks;
    }

    






    private static function buildAdditionalUserInfoList(int $destinationUserId): array
    {
        $destinationUser = User::findOne(['id' => $destinationUserId]);
        if (!$destinationUser) {
            return [];
        }

        $additionalUserInfoList = [
            [
                'label' => Yii::t('_partial/chat/additional_user_info', 'Подпись поля "ФИО" доп. информации об пользователе, в блоке заголовка контакта: `ФИО`'),
                'value' => $destinationUser->fullName,
            ],
            [
                'label' => Yii::t('_partial/chat/additional_user_info', 'Подпись поля "почта" доп. информации об пользователе, в блоке заголовка контакта: `Электронная почта`'),
                'value' => Html::a($destinationUser->email, "mailto:{$destinationUser->email}"),
            ],
        ];

        $additionalUserInfoList = AbiturientChatUser::buildAdditionalUserPhoneInfo($additionalUserInfoList, $destinationUser);
        $additionalUserInfoList = AbiturientChatUser::buildAdditionalUserApplicationsInfo($additionalUserInfoList, $destinationUser);

        return $additionalUserInfoList;
    }

    







    private static function buildAdditionalUserApplicationsInfo(array $additionalUserInfoList, User $destinationUser): array
    {
        $tnApplicationType = ApplicationType::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        $applicationTypes = ApplicationType::find()
            ->select(["{$tnApplicationType}.name"])
            ->joinWith('applications')
            ->andWhere([
                "{$tnApplicationType}.archive" => false,
                "{$tnBachelorApplication}.archive" => false,
                "{$tnBachelorApplication}.user_id" => $destinationUser->id,
            ])
            ->groupBy(["{$tnApplicationType}.name"])
            ->all();
        if ($applicationTypes) {
            $applications = trim( 
                array_reduce( 
                    $applicationTypes,
                    function ($carry, ApplicationType $applicationType) {
                        return $carry .= "{$applicationType->name}, ";
                    }
                ),
                ', '
            );
            if (!empty($applications)) {
                $additionalUserInfoList[] = [
                    'label' => Yii::t('_partial/chat/additional_user_info', 'Подпись поля "ПК" доп. информации об пользователе, в блоке заголовка контакта: `Приёмные кампании`'),
                    'value' => $applications,
                ];
            }
        }

        return $additionalUserInfoList;
    }

    







    private static function buildAdditionalUserPhoneInfo(array $additionalUserInfoList, User $destinationUser): array
    {
        
        $personalData = ArrayHelper::getValue($destinationUser, 'abiturientQuestionary.personalData');
        if ($personalData) {
            $phones = trim( 
                array_reduce( 
                    [$personalData->main_phone, $personalData->secondary_phone],
                    function ($carry, $phone) {
                        if (!$phone) {
                            return $carry;
                        }

                        $linkPhone = Html::a($phone, "tel:{$phone}");
                        return $carry .= "{$linkPhone}, ";
                    }
                ),
                ', '
            );
            if (!empty($phones)) {
                $additionalUserInfoList[] = [
                    'label' => Yii::t('_partial/chat/additional_user_info', 'Подпись поля "телефоны" доп. информации об пользователе, в блоке заголовка контакта: `Контактные телефоны`'),
                    'value' => $phones,
                ];
            }
        }

        return $additionalUserInfoList;
    }
}
