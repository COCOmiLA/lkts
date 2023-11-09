<?php

namespace common\modules\abiturient\models\bachelor;

use backend\components\ApplicationTypeHistoryTrait;
use common\components\queries\ArchiveQuery;
use common\models\interfaces\IArchiveQueryable;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;









class SearchApplicationType extends ApplicationType implements IArchiveQueryable
{
    use ApplicationTypeHistoryTrait;

    


    public $campaignArchive;

    public function afterFind()
    {
        parent::afterFind();
        $this->campaignArchive = $this->isCampaignArchive();
    }

    




    public function rules()
    {
        return [
            [['campaign_id'], 'integer'],
            [['name'], 'string'],
            [['archive', 'campaignArchive'], 'boolean']
        ];
    }

    




    public function attributeLabels()
    {
        return [
            'campaign_id' => 'Приемная кампания 1С',
            'campaignArchive' => 'Статус приемной кампании 1С',
            'archive' => 'Архив',
            'name' => 'Наименование ПК'
        ];
    }

    public function getCampaign()
    {
        return $this->getRawCampaign()->andOnCondition([AdmissionCampaign::tableName() . '.archive' => false]);
    }

    public function getRawCampaign()
    {
        return $this->hasOne(AdmissionCampaign::class, ['id' => 'campaign_id']);
    }

    public function getCampaignName()
    {
        if (isset($this->campaign)) {
            return $this->campaign->name;
        } elseif ($this->isCampaignArchive()) {
            return $this->rawCampaign->name;
        } else {
            return '';
        }
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    






    public function search($params)
    {
        $query = SearchApplicationType::find()->andWhere(['application_type.archive' => false]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'campaign_id' => [
                    'asc' => ['campaign_id' => SORT_ASC],
                    'desc' => ['campaign_id' => SORT_DESC]
                ],
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC]
                ],
                'archive' => [
                    'asc' => ['archive' => SORT_ASC],
                    'desc' => ['archive' => SORT_DESC]
                ],
            ]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if (isset($params['SearchApplicationType'])) {
            foreach ($params['SearchApplicationType'] as $key => $param) {
                if ($key === 'campaignArchive') {
                    if ($param !== "") {
                        $query->leftJoin('admission_campaign ac', 'ac.id = application_type.campaign_id')
                            ->andWhere([
                                'ac.archive' => $param
                            ]);
                    }
                } else {
                    $query->andWhere(['or like', 'application_type.' . $key, $param]);
                }
            }
        }
        return $dataProvider;
    }

    public function isCampaignArchive()
    {
        return (bool) ArrayHelper::getValue($this, 'rawCampaign.archive', true);
    }
}
