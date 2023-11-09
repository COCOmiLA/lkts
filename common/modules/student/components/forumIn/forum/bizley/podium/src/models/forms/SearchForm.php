<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Category;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Forum;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Post;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Vocabulary;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;








class SearchForm extends Model
{
    


    public $query;

    


    public $match;

    


    public $author;

    


    public $dateFrom;

    



    public $dateFromStamp;

    


    public $dateTo;

    



    public $dateToStamp;

    


    public $forums;

    


    public $type;

    


    public $display;

    


    public function rules()
    {
        return [
            [['query', 'author'], 'string'],
            [['query', 'author'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value));
            }],
            ['query', 'string', 'min' => 3],
            ['author', 'string', 'min' => 2],
            [['match'], 'in', 'range' => ['all', 'any']],
            [['dateFrom', 'dateTo'], 'default', 'value' => null],
            ['dateFrom', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'dateFromStamp'],
            ['dateTo', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'dateToStamp'],
            [['dateFromStamp', 'dateToStamp'], 'integer'],
            [['forums'], 'each', 'rule' => ['integer']],
            [['type', 'display'], 'in', 'range' => ['posts', 'topics']],
        ];
    }

    





    protected function prepareQuery($query, $topics = false)
    {
        $field = $topics
            ? Thread::tableName() . '.created_at'
            : Post::tableName() . '.updated_at';
        if (!empty($this->author)) {
            $query->andWhere(['like', 'username', $this->author])->joinWith(['author']);
        }
        if (!empty($this->dateFromStamp) && empty($this->dateToStamp)) {
            $query->andWhere(['>=', $field, $this->dateFromStamp]);
        } elseif (!empty($this->dateToStamp) && empty($this->dateFromStamp)) {
            $this->dateToStamp += 23 * 3600 + 59 * 60 + 59; 
            $query->andWhere(['<=', $field, $this->dateToStamp]);
        } elseif (!empty($this->dateToStamp) && !empty($this->dateFromStamp)) {
            if ($this->dateFromStamp > $this->dateToStamp) {
                $tmp = $this->dateToStamp;
                $this->dateToStamp = $this->dateFromStamp;
                $this->dateFromStamp = $tmp;
            }
            $this->dateToStamp += 23 * 3600 + 59 * 60 + 59; 
            $query->andWhere(['<=', $field, $this->dateToStamp]);
            $query->andWhere(['>=', $field, $this->dateFromStamp]);
        }
        if (!empty($this->forums)) {
            if (is_array($this->forums)) {
                $forums = [];
                foreach ($this->forums as $f) {
                    if (is_numeric($f)) {
                        $forums[] = (int)$f;
                    }
                }
                if (!empty($forums)) {
                    $query->andWhere(['forum_id' => $forums]);
                }
            }
        }
    }

    




    protected function searchTopics()
    {
        $query = Thread::find();
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['forum' => function ($q) {
                $q->andWhere([Forum::tableName() . '.visible' => 1])->joinWith(['category' => function ($q) {
                    $q->andWhere([Category::tableName() . '.visible' => 1]);
                }]);
            }]);
        }
        if (!empty($this->query)) {
            $words = explode(' ', preg_replace('/\s+/', ' ', $this->query));
            foreach ($words as $word) {
                if ($this->match == 'all') {
                    $query->andWhere(['like', Thread::tableName() . '.name', $word]);
                } else {
                    $query->orWhere(['like', Thread::tableName() . '.name', $word]);
                }
            }
        }
        $this->prepareQuery($query, true);
        $sort = [
            'defaultOrder' => [Thread::tableName() . '.id' => SORT_DESC],
            'attributes' => [
                Thread::tableName() . '.id' => [
                    'asc' => [Thread::tableName() . '.id' => SORT_ASC],
                    'desc' => [Thread::tableName() . '.id' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
            ]
        ];
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort,
        ]);
    }

    




    public function searchPosts()
    {
        $query = Vocabulary::find()->select('post_id, thread_id')->joinWith(['posts.author', 'posts.thread'])->andWhere(['is not', 'post_id', null]);
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['posts.forum' => function ($q) {
                $q->andWhere([Forum::tableName() . '.visible' => 1])->joinWith(['category' => function ($q) {
                    $q->andWhere([Category::tableName() . '.visible' => 1]);
                }]);
            }]);
        }
        if (!empty($this->query)) {
            $words = explode(' ', preg_replace('/\s+/', ' ', $this->query));
            $countWords = 0;
            foreach ($words as $word) {
                $query->orWhere(['like', 'word', $word]);
                $countWords++;
            }
            $query->groupBy(['post_id', 'thread_id']);
            if ($this->match == 'all' && $countWords > 1) {
                $query->having(['>', 'COUNT(post_id)', $countWords - 1]);
            }
        }
        $this->prepareQuery($query);
        $sort = [
            'defaultOrder' => ['post_id' => SORT_DESC],
            'attributes' => [
                'post_id' => [
                    'asc' => ['post_id' => SORT_ASC],
                    'desc' => ['post_id' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
            ]
        ];
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort,
        ]);
    }

    



    public function searchAdvanced()
    {
        if ($this->type == 'topics') {
            return $this->searchTopics();
        }
        return $this->searchPosts();
    }
}
