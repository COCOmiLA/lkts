<?php

namespace backend\modules\i18n\models\search;

use backend\modules\i18n\models\I18nSourceMessage;
use common\components\LikeQueryManager;
use yii\base\Model;
use yii\data\ActiveDataProvider;




class I18nSourceMessageSearch extends I18nSourceMessage
{
    


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['category', 'message'], 'safe'],
        ];
    }

    


    public function scenarios()
    {
        
        return Model::scenarios();
    }

    






    public function search($params)
    {
        $query = I18nSourceMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            '{{%i18n_source_message}}.id' => $this->id,
        ]);

        $query->andFilterWhere([LikeQueryManager::getActionName(), '{{%i18n_source_message}}.category', $this->category])
            ->andFilterWhere([LikeQueryManager::getActionName(), '{{%i18n_source_message}}.message', $this->message]);

        return $dataProvider;
    }
}
