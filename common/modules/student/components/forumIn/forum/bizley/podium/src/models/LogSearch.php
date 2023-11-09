<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;







class LogSearch extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_log}}';
    }

    


    public function rules()
    {
        return [
            [['id', 'level', 'model', 'user'], 'integer'],
            [['category', 'ip', 'message'], 'string'],
        ];
    }

    




    public function search($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['level' => $this->level])
            ->andFilterWhere(['model' => $this->model])
            ->andFilterWhere(['user' => $this->user])
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
