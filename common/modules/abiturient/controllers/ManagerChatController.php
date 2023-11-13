<?php

namespace common\modules\abiturient\controllers;

use common\models\User;
use common\modules\abiturient\models\chat\AbiturientChatUser;
use common\modules\abiturient\models\chat\ChatPersonToPerson;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class ManagerChatController extends BaseChatController
{
    public function init()
    {
        parent::init();

        Yii::setAlias('@chatHeaderView', '@chatPartialView/manager-chat-destination-head-temp');
    }

    public static function otherUsersClass(): string
    {
        return AbiturientChatUser::class;
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'download',
                            'end-chat',
                            'manager-index',
                            'manager-update-chat-people-list',
                            'open-chat',
                            'redirect-chat',
                            'send-message',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER]
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'end-chat' => ['POST'],
                    'open-chat' => ['POST'],
                    'send-message' => ['POST'],

                    'download' => ['GET'],
                    'manager-index' => ['GET'],
                    'manager-update-chat-people-list' => ['GET'],
                    'redirect-chat' => ['GET'],
                ]
            ],
        ];
    }

    public function getViewPath()
    {
        return Yii::getAlias('@chatView');
    }

    public function actionManagerIndex()
    {
        return parent::renderActionIndex('manager-index');
    }

    


    public function processDataActionIndex(): array
    {
        $user = Yii::$app->user->identity;

        $availableUsersWithChatsQuery       = AbiturientChatUser::getAvailableUsersWithChatsQuery($user);
        $availableUsersWithoutChatsQuery    = AbiturientChatUser::getAvailableUsersWithoutChatsQuery($user);
        $availableUsersWithEndingChatsQuery = AbiturientChatUser::getAvailableUsersWithEndingChatsQuery($user);

        $searchModel = AbiturientChatUser::getSearchModel($user);
        if ($searchModel->load(Yii::$app->request->get())) {
            $availableUsersWithChatsQuery       = AbiturientChatUser::filteringChatUserBySearchModel($availableUsersWithChatsQuery,       $searchModel);
            $availableUsersWithoutChatsQuery    = AbiturientChatUser::filteringChatUserBySearchModel($availableUsersWithoutChatsQuery,    $searchModel);
            $availableUsersWithEndingChatsQuery = AbiturientChatUser::filteringChatUserBySearchModel($availableUsersWithEndingChatsQuery, $searchModel);
        }
        $availableUsersWithChats       = $availableUsersWithChatsQuery->all();
        $availableUsersWithoutChats    = $availableUsersWithoutChatsQuery->all();
        $availableUsersWithEndingChats = $availableUsersWithEndingChatsQuery->all();

        return [
            'user' => $user,
            'searchModel' => $searchModel,
            'availableUsersWithChats' => $availableUsersWithChats,
            'availableUsersWithoutChats' => $availableUsersWithoutChats,
            'availableUsersWithEndingChats' => $availableUsersWithEndingChats,
        ];
    }

    public function actionManagerUpdateChatPeopleList()
    {
        return parent::renderActionUpdateChatPeopleList('@chatPartialView/manager-chat-people-list');
    }

    public function actionEndChat(int $destination_id)
    {
        Yii::$app->response->statusCode = 400;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isAjax) {
            $thisUser = Yii::$app->user->identity;

            $chat = ChatPersonToPerson::findChatByIdAndUsersId(
                (int) Yii::$app->request->post('chatId'),
                [$thisUser->id, $destination_id]
            );

            if ($chat && $chat->endChat($thisUser->id)) {
                Yii::$app->response->statusCode = 200;

                return ['success' => true];
            }
        }
    }

    public function actionRedirectChat(int $destination_id, int $other_manager_id)
    {
        $thisUser = Yii::$app->user->identity;
        $otherManager = User::findOne(['id' => $other_manager_id]);

        $chat = ChatPersonToPerson::findChatUsersId([
            $thisUser->id,
            $destination_id
        ]);

        if ($chat && $chat->redirectChat($thisUser, $otherManager)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t(
                    'manager/chat/redirect-chat',
                    'Текст при удачном перенаправлении чата на другого модератора, на странице чата: `Переадресация произошла успешно.`'
                ),
                'options' => ['class' => 'alert-success']
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t(
                    'manager/chat/redirect-chat',
                    'Текст при не удачном перенаправлении чата на другого модератора, на странице чата: `Переадресация завершилась с ошибкой. Повторите попытку позже.`'
                ),
                'options' => ['class' => 'alert-danger']
            ]);
        }

        return $this->redirect(['manager-index']);
    }
}
