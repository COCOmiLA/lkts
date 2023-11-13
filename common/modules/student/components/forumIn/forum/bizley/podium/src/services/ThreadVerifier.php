<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\services;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Category;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Forum;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\base\Component;







class ThreadVerifier extends Component
{
    


    public $categoryId;

    


    public $forumId;

    


    public $threadId;

    


    public $threadSlug;

    



    public function validate()
    {
        if (is_numeric($this->categoryId) && $this->categoryId >= 1
                && is_numeric($this->forumId) && $this->forumId >= 1
                && is_numeric($this->threadId) && $this->threadId >= 1
                && !empty($this->threadSlug)) {
            return true;
        }
        return false;
    }

    private $_query;

    



    public function verify()
    {
        if (!$this->validate()) {
            return null;
        }
        $this->_query = Thread::find()->where([
                            Thread::tableName() . '.id' => $this->threadId,
                            Thread::tableName() . '.slug' => $this->threadSlug,
                            Thread::tableName() . '.forum_id' => $this->forumId,
                            Thread::tableName() . '.category_id' => $this->categoryId,
                        ]);
        if (Podium::getInstance()->user->isGuest) {
            return $this->getThreadForGuests();
        }
        return $this->getThreadForMembers();
    }

    



    protected function getThreadForGuests()
    {
        return $this->_query->joinWith([
                'forum' => function ($query) {
                    $query->andWhere([
                            Forum::tableName() . '.visible' => 1
                        ])->joinWith(['category' => function ($query) {
                            $query->andWhere([Category::tableName() . '.visible' => 1]);
                        }]);
                }
            ])->limit(1)->one();
    }

    



    protected function getThreadForMembers()
    {
        return $this->_query->joinWith('forum.category')->limit(1)->one();
    }
}
