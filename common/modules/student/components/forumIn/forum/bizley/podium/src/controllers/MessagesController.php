<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\controllers;

use common\modules\student\components\forumIn\forum\bizley\podium\src\filters\AccessControl;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Message;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\MessageReceiver;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\MessageSearch;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\helpers\Json;
use yii\web\Response;








class MessagesController extends BaseController
{
    


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function ($rule, $action) {
                    return $this->redirect(['account/login']);
                },
                'rules' => [
                    ['class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\filters\InstallRule'],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    




    public function actions()
    {
        return [
            'delete-received' => [
                'class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\actions\MessageAction',
                'redirectRoute' => ['messages/inbox'],
                'type' => 'receiver',
            ],
            'delete-sent' => [
                'class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\actions\MessageAction',
                'redirectRoute' => ['messages/sent'],
                'type' => 'sender',
            ],
        ];
    }

    



    public function actionInbox()
    {
        $searchModel = new MessageReceiver();
        return $this->render('inbox', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel' => $searchModel
        ]);
    }

    




    public function actionNew($user = null)
    {
        $podiumUser = User::findMe();

        if (Message::tooMany($podiumUser->id)) {
            $this->warning(Yii::t('podium/flash', 'You have reached maximum {max_messages, plural, =1{ message} other{ messages}} per {max_minutes, plural, =1{ minute} other{ minutes}} limit. Wait few minutes before sending a new message.', [
                'max_messages' => Message::SPAM_MESSAGES,
                'max_minutes' => Message::SPAM_WAIT
            ]));
            return $this->redirect(['messages/inbox']);
        }

        $model = new Message();
        $to = null;
        if (!empty($user) && (int)$user > 0 && (int)$user != $podiumUser->id) {
            $member = User::find()->where(['id' => (int)$user, 'status' => User::STATUS_ACTIVE])->limit(1)->one();
            if ($member) {
                $model->receiversId = [$member->id];
                $to = $member;
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $validated = [];
                $errors = false;
                if (!empty($model->friendsId)) {
                    $model->receiversId = array_merge(
                        is_array($model->receiversId) ? $model->receiversId : [],
                        is_array($model->friendsId) ? $model->friendsId : []
                    );
                }
                if (empty($model->receiversId)) {
                    $model->addError('receiversId', Yii::t('podium/view', 'You have to select at least one message receiver.'));
                    $errors = true;
                } else {
                    foreach ($model->receiversId as $r) {
                        if ($r == $podiumUser->id) {
                            $model->addError('receiversId', Yii::t('podium/view', 'You can not send message to yourself.'));
                            $errors = true;
                        } elseif ($podiumUser->isIgnoredBy($r)) {
                            $model->addError('receiversId', Yii::t('podium/view', 'One of the selected members ignores you and has been removed from message receivers.'));
                            $errors = true;
                        } else {
                            $member = User::find()->where(['id' => (int)$r, 'status' => User::STATUS_ACTIVE])->limit(1)->one();
                            if ($member) {
                                $validated[] = $member->id;
                                if (count($validated) > Message::MAX_RECEIVERS) {
                                    $model->addError('receiversId', Yii::t('podium/view', 'You can send message up to a maximum of 10 receivers at once.'));
                                    $errors = true;
                                    break;
                                }
                            }
                        }
                    }
                    $model->receiversId = $validated;
                }
                if (!$errors) {
                    if ($model->send()) {
                        $this->success(Yii::t('podium/flash', 'Message has been sent.'));
                        return $this->redirect(['messages/inbox']);
                    }
                    $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending your message.'));
                }
            }
        }
        return $this->render('new', ['model' => $model, 'to' => $to, 'friends' => User::friendsList()]);
    }

    




    public function actionReply($id = null)
    {
        $podiumUser = User::findMe();

        if (Message::tooMany($podiumUser->id)) {
            $this->warning(Yii::t('podium/flash', 'You have reached maximum {max_messages, plural, =1{ message} other{ messages}} per {max_minutes, plural, =1{ minute} other{ minutes}} limit. Wait few minutes before sending a new message.', [
                'max_messages' => Message::SPAM_MESSAGES,
                'max_minutes' => Message::SPAM_WAIT
            ]));
            return $this->redirect(['messages/inbox']);
        }

        $reply = Message::find()->where([Message::tableName() . '.id' => $id])->joinWith([
                'messageReceivers' => function ($q) use ($podiumUser) {
                    $q->where(['receiver_id' => $podiumUser->id]);
                }])->limit(1)->one();
        if (empty($reply)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->redirect(['messages/inbox']);
        }

        $model = new Message();
        $model->topic = Message::re() . ' ' . $reply->topic;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (!$podiumUser->isIgnoredBy($model->receiversId[0])) {
                    $model->replyto = $reply->id;
                    if ($model->send()) {
                        $this->success(Yii::t('podium/flash', 'Message has been sent.'));
                        return $this->redirect(['messages/inbox']);
                    }
                    $this->error(Yii::t('podium/flash', 'Sorry! There was some error while sending your message.'));
                } else {
                    $this->error(Yii::t('podium/flash', 'Sorry! This member ignores you so you can not send the message.'));
                }
            }
        }
        $model->receiversId = [$reply->sender_id];
        return $this->render('reply', ['model' => $model, 'reply' => $reply]);
    }

    



    public function actionSent()
    {
        $searchModel = new MessageSearch();
        return $this->render('sent', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel' => $searchModel
        ]);
    }

    




    public function actionViewSent($id = null)
    {
        $model = Message::find()->where([
                'and',
                ['id' => $id, 'sender_id' => User::loggedId()],
                ['!=', 'sender_status', Message::STATUS_DELETED]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->redirect(['messages/inbox']);
        }
        $model->markRead();
        return $this->render('view', [
            'model' => $model,
            'type' => 'sent',
            'id' => $model->id
        ]);
    }

    




    public function actionViewReceived($id = null)
    {
        $model = MessageReceiver::find()->where([
                'and',
                ['id' => $id, 'receiver_id' => User::loggedId()],
                ['!=', 'receiver_status', MessageReceiver::STATUS_DELETED]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->redirect(['messages/inbox']);
        }
        $model->markRead();
        return $this->render('view', [
            'model' => $model->message,
            'type' => 'received',
            'id' => $model->id
        ]);
    }

    



    public function actionLoad()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['forum/index']);
        }

        $result = ['messages' => '', 'more' => 0];

        if (!Podium::getInstance()->user->isGuest) {
            $loggedId = User::loggedId();
            $id = Yii::$app->request->post('message');
            $message = Message::find()->where(['id' => $id])->limit(1)->one();
            if ($message && ($message->sender_id == $loggedId || $message->isMessageReceiver($loggedId))) {
                $stack = 0;
                $reply = clone $message;
                while ($reply->reply && $stack < 5) {
                    $result['more'] = 0;
                    if ($reply->reply->sender_id == $loggedId && $reply->reply->sender_status == Message::STATUS_DELETED) {
                        $reply = $reply->reply;
                        continue;
                    }
                    $result['messages'] .= $this->renderPartial('load', ['reply' => $reply]);
                    $reply = $reply->reply;
                    if ($reply) {
                        $result['more'] = $reply->id;
                    }
                    $stack++;
                }
            }
        }
        return Json::encode($result);
    }
}
