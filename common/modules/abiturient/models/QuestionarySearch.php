<?php

namespace common\modules\abiturient\models;

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\LikeQueryManager;
use common\models\query\ActiveRecordDataProvider;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;

class QuestionarySearch extends AbiturientQuestionary
{
    public function rules()
    {
        return [[
            [
                'fio',
                'usermail',
                'created_at',
            ],
            'safe'
        ]];
    }

    public $fio;
    public $usermail;
    public $created_at;

    public function search($params)
    {
        $tnUser = User::tableName();
        $tnPersonalData = PersonalData::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        $tnAbiturientQuestionary = AbiturientQuestionary::tableName();

        $questionaries_with_created_apps = AbiturientQuestionary::find()
            ->joinWith(['user'])
            ->leftJoin($tnBachelorApplication, "{$tnUser}.id = {$tnBachelorApplication}.user_id")
            ->select(["{$tnAbiturientQuestionary}.id"])
            ->groupBy(["{$tnAbiturientQuestionary}.id"])
            ->having(["MAX({$tnBachelorApplication}.draft_status)" => IDraftable::DRAFT_STATUS_CREATED]);

        
        $query = AbiturientQuestionary::find()
            ->joinWith(['user'])
            ->joinWith(['personalData'])
            ->leftJoin($tnBachelorApplication, "{$tnUser}.id = {$tnBachelorApplication}.user_id")
            ->andWhere(["{$tnAbiturientQuestionary}.archive" => false])
            ->andWhere(["{$tnBachelorApplication}.archive" => false])
            ->andWhere([
                'OR',
                ["{$tnBachelorApplication}.id" => null],
                ["{$tnAbiturientQuestionary}.id" => $questionaries_with_created_apps->column()]
            ]);

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere([
                LikeQueryManager::getActionName(),
                IndependentQueryManager::toDate("{$tnAbiturientQuestionary}.created_at"),
                trim((string) $this->created_at)
            ]);
            $query->andFilterWhere([LikeQueryManager::getActionName(), "{$tnUser}.email", trim((string) $this->usermail)]);

            $query->andFilterWhere([LikeQueryManager::getActionName(), "{$tnPersonalData}.lastname", trim((string) $this->fio)]);
        }

        $dataProvider = new ActiveRecordDataProvider([
            'query' => $query,
            'primary_column' => "{$tnAbiturientQuestionary}.id",
            'pagination' => ['pagesize' => 20],
        ]);

        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_ASC],
            'attributes' => [
                'id',
                'fio' => [
                    'asc' => ["{$tnPersonalData}.lastname" => SORT_ASC],
                    'desc' => ["{$tnPersonalData}.lastname" => SORT_DESC],
                    'label' => 'ФИО'
                ],
                'usermail' => [
                    'asc' => ["{$tnUser}.email" => SORT_ASC],
                    'desc' => ["{$tnUser}.email" => SORT_DESC],
                    'label' => 'Email'
                ],
                'created_at' => [
                    'asc' => ["{$tnAbiturientQuestionary}.created_at" => SORT_ASC],
                    'desc' => ["{$tnAbiturientQuestionary}.created_at" => SORT_DESC],
                ],
            ]
        ]);
        return $dataProvider;
    }
}
