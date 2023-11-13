<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\VocabularyActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\data\ActiveDataProvider;








class Vocabulary extends VocabularyActiveRecord
{
    



    public function search()
    {
        $th = static::tableName();
        $query = static::find()->where([
            'and',
            ['is not', 'post_id', null],
            ['like', 'word', $this->query]
        ])->joinWith(['posts.author', 'posts.thread']);
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['posts.forum' => function ($q) {
                $q->where([Forum::tableName() . '.visible' => 1]);
            }]);
        }
        $query->groupBy(["{$th}.id", 'post_id']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['thread_id' => SORT_DESC],
                'attributes' => [
                    'thread_id' => [
                        'asc' => ['thread_id' => SORT_ASC],
                        'desc' => ['thread_id' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ]
            ],
        ]);

        return $dataProvider;
    }
}
