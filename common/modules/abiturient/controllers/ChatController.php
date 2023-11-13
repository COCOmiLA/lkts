<?php

namespace common\modules\abiturient\controllers;

use common\models\User;
use common\modules\abiturient\models\chat\ManagerChatUser;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ChatController extends BaseChatController
{
    public function init()
    {
        parent::init();

        Yii::setAlias('@chatHeaderView', '@chatPartialView/entrant-chat-destination-head-temp');
    }

    public static function otherUsersClass(): string
    {
        return ManagerChatUser::class;
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
                            'entrant-index',
                            'entrant-update-chat-people-list',
                            'open-chat',
                            'send-message',
                        ],
                        'allow' => true,
                        'roles' => [User::ROLE_ABITURIENT]
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'open-chat' => ['POST'],
                    'send-message' => ['POST'],

                    'download' => ['GET'],
                    'entrant-index' => ['GET'],
                    'entrant-update-chat-people-list' => ['GET'],
                ]
            ],
        ];
    }

    public function getViewPath()
    {
        return Yii::getAlias('@chatView');
    }

    public function actionEntrantIndex()
    {
        return parent::renderActionIndex('index');
    }

    public function actionEntrantUpdateChatPeopleList()
    {
        return parent::renderActionUpdateChatPeopleList('@chatPartialView/chat-people-list');
    }

    


    public function processDataActionIndex(): array
    {
        $data = parent::processDataActionIndex();

        $availableUsersWithEndingChats = ManagerChatUser::getAvailableUsersWithEndingChats($data['user']);

        return array_merge(
            $data,
            ['availableUsersWithEndingChats' => $availableUsersWithEndingChats]
        );
    }
}
