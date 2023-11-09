<?php

namespace backend\models\search;

use common\components\LikeQueryManager;
use filsh\yii2\oauth2server\models\OauthClients;
use yii\base\Model;
use yii\data\ActiveDataProvider;




class OauthSearch extends OauthClients
{
    


    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope'], 'safe'],
        ];
    }

    


    public function scenarios()
    {
        
        return Model::scenarios();
    }

    



    public function search($params)
    {
        $query = OauthClients::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere([LikeQueryManager::getActionName(), 'client_id', $this->client_id])
            ->andFilterWhere([LikeQueryManager::getActionName(), 'client_secret', $this->client_secret])
            ->andFilterWhere([LikeQueryManager::getActionName(), 'redirect_uri', $this->redirect_uri])
            ->andFilterWhere([LikeQueryManager::getActionName(), 'grant_types', $this->grant_types])
            ->andFilterWhere([LikeQueryManager::getActionName(), 'scope', $this->scope]);

        return $dataProvider;
    }
}
