<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\SubscriptionActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;







class Subscription extends SubscriptionActiveRecord
{
    


    const POST_SEEN = 1;
    const POST_NEW  = 0;

    



    public function search()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => static::find()->where(['user_id' => User::loggedId()]),
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);
        $dataProvider->sort->defaultOrder = ['post_seen' => SORT_ASC, 'id' => SORT_DESC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);
        return $dataProvider;
    }

    



    public function seen()
    {
        $this->post_seen = self::POST_SEEN;
        if (!$this->save()) {
            return false;
        }
        Podium::getInstance()->podiumCache->deleteElement('user.subscriptions', User::loggedId());
        return true;
    }

    



    public function unseen()
    {
        $this->post_seen = self::POST_NEW;
        if (!$this->save()) {
            return false;
        }
        Podium::getInstance()->podiumCache->deleteElement('user.subscriptions', User::loggedId());
        return true;
    }

    



    public static function notify($thread)
    {
        if (is_numeric($thread) && $thread > 0) {
            $forum = Podium::getInstance()->podiumConfig->get('name');
            $email = Content::fill(Content::EMAIL_SUBSCRIPTION);
            $subs = static::find()->where(['thread_id' => $thread, 'post_seen' => self::POST_SEEN]);
            foreach ($subs->each() as $sub) {
                $sub->post_seen = self::POST_NEW;
                if ($sub->save()) {
                    if ($email !== false && !empty($sub->user->email)) {
                        if (Email::queue(
                                $sub->user->email,
                                str_replace('{forum}', $forum, $email->topic),
                                str_replace('{forum}', $forum, str_replace('{link}', Html::a(
                                        Url::to(['forum/last', 'id' => $sub->thread_id], true),
                                        Url::to(['forum/last', 'id' => $sub->thread_id], true)
                                    ), $email->content)),
                                $sub->user_id
                            )) {
                            Log::info('Subscription notice link queued', $sub->user_id, __METHOD__);
                        } else {
                            Log::error('Error while queuing subscription notice link', $sub->user_id, __METHOD__);
                        }
                    } else {
                        Log::warning('Error while queuing subscription notice link - no email set', $sub->user_id, __METHOD__);
                    }
                }
            }
        }
    }

    





    public static function remove($threads = [])
    {
        try {
            if (!empty($threads)) {
                return Podium::getInstance()->db->createCommand()->delete(
                        Subscription::tableName(), ['id' => $threads, 'user_id' => User::loggedId()]
                    )->execute();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    





    public static function add($thread)
    {
        if (Podium::getInstance()->user->isGuest) {
            return false;
        }
        $sub = new Subscription();
        $sub->thread_id = $thread;
        $sub->user_id = User::loggedId();
        $sub->post_seen = self::POST_SEEN;
        return $sub->save();
    }
}
