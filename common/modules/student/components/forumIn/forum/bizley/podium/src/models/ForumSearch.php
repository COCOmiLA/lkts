<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\Query;
use yii\base\Model;
use yii\data\ActiveDataProvider;







class ForumSearch extends Forum
{
    


    public function rules()
    {
        return [['name', 'string']];
    }

    


    public function scenarios()
    {
        return Model::scenarios();
    }

    




    public function isMod($userId = null)
    {
        return (new Query())->from(Mod::tableName())->where(['forum_id' => $this->id, 'user_id' => $userId])->exists();
    }

    




    public function searchForMods($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
                'forcePageParam' => false
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['name' => SORT_ASC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['name' => $this->name]);

        return $dataProvider;
    }
}
