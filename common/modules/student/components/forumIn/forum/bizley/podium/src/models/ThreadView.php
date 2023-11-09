<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\ThreadViewActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;







class ThreadView extends ThreadViewActiveRecord
{
    




    public function search()
    {
        $loggedId = User::loggedId();
        $query = Thread::find()->joinWith(['threadView' => function ($q) use ($loggedId) {
            $q->onCondition(['user_id' => $loggedId]);
            $q->andWhere(['or',
                    new Expression('new_last_seen < new_post_at'),
                    new Expression('edited_last_seen < edited_post_at'),
                    ['new_last_seen' => null],
                    ['edited_last_seen' => null],
                ]);
        }], false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['edited_post_at' => SORT_ASC, 'id' => SORT_ASC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        return $dataProvider;
    }
}
