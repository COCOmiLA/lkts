<?php

namespace backend\modules\i18n\models\search;

use backend\modules\i18n\models\I18nMessage;
use common\components\LikeQueryManager;
use yii\base\Model;
use yii\data\ActiveDataProvider;




class I18nMessageSearch extends I18nMessage
{
    


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['language', 'translation', 'sourceMessage', 'category'], 'safe'],
        ];
    }

    


    public function scenarios()
    {
        
        return Model::scenarios();
    }

    






    public function search($params)
    {
        $query = I18nMessage::find()->with('sourceMessageModel')->joinWith('sourceMessageModel');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }



        $query->andFilterWhere([
            '{{%i18n_source_message}}.id' => $this->id
        ]);

        $query->andFilterWhere([LikeQueryManager::getActionName(), '{{%i18n_source_message}}.language', $this->language])
            ->andFilterWhere([LikeQueryManager::getActionName(), '{{%i18n_source_message}}.translation', $this->translation])
            ->andFilterWhere([LikeQueryManager::getActionName(), '{{%i18n_source_message}}.message', $this->sourceMessage])
            ->andFilterWhere([LikeQueryManager::getActionName(), '{{%i18n_source_message}}.category', $this->category]);


        return $dataProvider;
    }
}
