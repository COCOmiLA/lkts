<?php

namespace backend\models\search;

use backend\models\SystemLog;
use common\components\LikeQueryManager;
use yii\base\Model;
use yii\data\ActiveDataProvider;




class SystemLogSearch extends SystemLog
{
    


    public function rules()
    {
        return [
            [['id', 'log_time'], 'integer'],
            [['category', 'prefix', 'level'], 'safe'],
        ];
    }

    


    public function scenarios()
    {
        
        return Model::scenarios();
    }

    



    public function search($params)
    {
        $query = SystemLog::find()
            ->select(['id', 'level', 'category', 'log_time', 'prefix']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'level' => $this->level,
            'log_time' => $this->log_time,
        ]);

        $query->andFilterWhere([LikeQueryManager::getActionName(), 'category', $this->category])
            ->andFilterWhere([LikeQueryManager::getActionName(), 'prefix', $this->prefix]);

        return $dataProvider;
    }
}
