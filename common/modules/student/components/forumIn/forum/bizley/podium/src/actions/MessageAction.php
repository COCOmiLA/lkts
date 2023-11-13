<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\actions;

use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Message;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\MessageReceiver;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use Yii;
use yii\base\Action;
use yii\db\ActiveQuery;
use yii\web\Response;







class MessageAction extends Action
{
    


    public $redirectRoute;

    


    public $type;

    



    public function getDeletedStatus()
    {
        if ($this->type == 'sender') {
            return Message::STATUS_DELETED;
        }
        return MessageReceiver::STATUS_DELETED;
    }

    



    public function getModelQuery()
    {
        if ($this->type == 'sender') {
            return Message::find();
        }
        return MessageReceiver::find();
    }

    




    public function run($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the message you are looking for.'));
            return $this->controller->redirect($this->redirectRoute);
        }
        $model = $this->modelQuery->where([
                'and',
                ['id' => $id, $this->type . '_id' => User::loggedId()],
                ['!=', $this->type . '_status', $this->deletedStatus]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the message with the given ID.'));
            return $this->controller->redirect($this->redirectRoute);
        }
        if ($model->remove()) {
            $this->controller->success(Yii::t('podium/flash', 'Message has been deleted.'));
        } else {
            Log::error('Error while deleting message', $model->id, __METHOD__);
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not delete this message. Contact administrator about this problem.'));
        }
        return $this->controller->redirect($this->redirectRoute);
    }
}
