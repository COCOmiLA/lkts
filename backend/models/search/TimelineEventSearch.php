<?php

namespace backend\models\search;

use common\components\LikeQueryManager;
use common\models\TimelineEvent;
use yii\base\Model;
use yii\data\ActiveDataProvider;




class TimelineEventSearch extends TimelineEvent
{
    


    public function rules()
    {
        return [
            [['application', 'category', 'event', 'created_at'], 'safe'],
        ];
    }

    


    public function scenarios()
    {
        
        return Model::scenarios();
    }

    






    public function search($params)
    {
        $query = TimelineEvent::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere([LikeQueryManager::getActionName(), 'application', $this->application]);
        $query->andFilterWhere([LikeQueryManager::getActionName(), 'category', $this->category]);
        $query->andFilterWhere([LikeQueryManager::getActionName(), 'event', $this->event]);

        return $dataProvider;
    }
}
