<?php








namespace backend\models\search;

use backend\models\SystemLogInfo;
use common\components\LikeQueryManager;
use yii\base\Model;
use yii\data\ActiveDataProvider;




class SystemLogInfoSearch extends SystemLogInfo
{
    


    public function rules()
    {
        return [
            [['id', 'log_time', 'message'], 'integer'],
            [['category', 'prefix', 'level'], 'safe'],
        ];
    }

    


    public function scenarios()
    {
        
        return Model::scenarios();
    }

    



    public function search($params)
    {
        $query = SystemLogInfo::find();

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
            'message' => $this->message,
        ]);

        $query->andFilterWhere([LikeQueryManager::getActionName(), 'category', $this->category])
            ->andFilterWhere([LikeQueryManager::getActionName(), 'prefix', $this->prefix]);

        return $dataProvider;
    }
}
