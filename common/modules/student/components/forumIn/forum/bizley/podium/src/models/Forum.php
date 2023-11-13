<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\Query;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\ForumActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\services\Sorter;
use yii\data\ActiveDataProvider;







class Forum extends ForumActiveRecord
{
    



    public function getMods()
    {
        $mods = Podium::getInstance()->podiumCache->getElement('forum.moderators', $this->id);
        if ($mods === false) {
            $mods = [];
            $modteam = User::find()->select(['id', 'role'])->where([
                    'status' => User::STATUS_ACTIVE,
                    'role' => [User::ROLE_ADMIN, User::ROLE_MODERATOR]
                ]);
            foreach ($modteam->each() as $user) {
                if ($user->role == User::ROLE_ADMIN) {
                    $mods[] = $user->id;
                    continue;
                }
                if ((new Query())->from(Mod::tableName())->where([
                        'forum_id' => $this->id, 'user_id' => $user->id
                    ])->exists()) {
                    $mods[] = $user->id;
                }
            }
            Podium::getInstance()->podiumCache->setElement('forum.moderators', $this->id, $mods);
        }
        return $mods;
    }

    




    public function isMod($userId = null)
    {
        if (in_array($userId ?: User::loggedId(), $this->getMods())) {
            return true;
        }
        return false;
    }

    




    public function search($categoryId = null, $onlyVisible = false)
    {
        $query = static::find();
        if ($categoryId) {
            $query->andWhere(['category_id' => $categoryId]);
        }
        if ($onlyVisible) {
            $query->joinWith(['category' => function ($query) {
                $query->andWhere([Category::tableName() . '.visible' => 1]);
            }]);
            $query->andWhere([static::tableName() . '.visible' => 1]);
        }

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider;
    }

    








    public static function verify($categoryId = null, $id = null, $slug = null, $guest = true)
    {
        if (!is_numeric($categoryId) || $categoryId < 1 || !is_numeric($id) || $id < 1 || empty($slug)) {
            return null;
        }
        return static::find()->joinWith(['category' => function ($query) use ($guest) {
                if ($guest) {
                    $query->andWhere([Category::tableName() . '.visible' => 1]);
                }
            }])->where([
                static::tableName() . '.id' => $id,
                static::tableName() . '.slug' => $slug,
                static::tableName() . '.category_id' => $categoryId,
            ])->limit(1)->one();
    }

    





    public function newOrder($order)
    {
        $sorter = new Sorter();
        $sorter->target = $this;
        $sorter->order = $order;
        $sorter->query = (new Query())
                            ->from(static::tableName())
                            ->where(['and',
                                ['!=', 'id', $this->id],
                                ['category_id' => $this->category_id]
                            ])
                            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                            ->indexBy('id');
        return $sorter->run();
    }
}
