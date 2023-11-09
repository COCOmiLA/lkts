<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\actions;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\services\ThreadVerifier;
use Yii;
use yii\base\Action;
use yii\web\Response;







class ThreadAction extends Action
{
    


    public $permission;

    


    public $boolAttribute;

    


    public $switcher;

    


    public $onMessage;

    


    public $offMessage;

    private $_thread;

    







    public function getThread($cid, $fid, $id, $slug)
    {
        if ($this->_thread === null) {
            $this->_thread = (new ThreadVerifier([
                    'categoryId' => $cid,
                    'forumId' => $fid,
                    'threadId' => $id,
                    'threadSlug' => $slug
                ]))->verify();
        }
        return $this->_thread;
    }

    







    public function run($cid = null, $fid = null, $id = null, $slug = null)
    {
        $thread = $this->getThread($cid, $fid, $id, $slug);
        if (empty($thread)) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->controller->redirect(['forum/index']);
        }

        if (!User::can($this->permission, ['item' => $thread])) {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->controller->redirect(['forum/index']);
        }

        if (call_user_func([$thread, $this->switcher])) {
            $this->controller->success($thread->{$this->boolAttribute} ? $this->onMessage : $this->offMessage);
        } else {
            $this->controller->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the thread.'));
        }
        return $this->controller->redirect([
            'forum/thread',
            'cid' => $thread->forum->category->id,
            'fid' => $thread->forum->id,
            'id' => $thread->id,
            'slug' => $thread->slug
        ]);
    }
}
