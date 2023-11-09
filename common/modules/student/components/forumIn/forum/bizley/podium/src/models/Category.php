<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\Query;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\CategoryActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\services\Sorter;
use yii\data\ActiveDataProvider;







class Category extends CategoryActiveRecord
{
    



    public function search()
    {
        $query = static::find();
        if (Podium::getInstance()->user->isGuest) {
            $query->andWhere(['visible' => 1]);
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider;
    }

    



    public function show()
    {
        $dataProvider = new ActiveDataProvider(['query' => static::find()]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider->getModels();
    }

    





    public function newOrder($order)
    {
        $sorter = new Sorter();
        $sorter->target = $this;
        $sorter->order = $order;
        $sorter->query = (new Query())
                            ->from(static::tableName())
                            ->where(['!=', 'id', $this->id])
                            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                            ->indexBy('id');
        return $sorter->run();
    }
}
