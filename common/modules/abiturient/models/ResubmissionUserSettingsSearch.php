<?php

namespace common\modules\abiturient\models;

use common\components\BooleanCaster;
use common\components\LikeQueryManager;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\EmptyCheck;
use common\models\User;
use common\models\UserProfile;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\ApplicationTypeSettings;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class ResubmissionUserSettingsSearch extends Model
{
    public $pageSize;
    public $fio = '';
    public $email = '';
    public $campaign_ref_uid = '';
    public $allow_resubmit = null;

    public function rules()
    {
        return [
            [['pageSize'], 'integer'],
            [
                [
                    'email',
                    'fio',
                    'campaign_ref_uid',
                    'allow_resubmit',
                ],
                'safe'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fio' => Yii::t('common/modules/abiturient/resubmission-search', 'Подпись для поля "fio" фильтров для управления повторной подачей заявлений: `ФИО`'),
            'email' => Yii::t('common/modules/abiturient/resubmission-search', 'Подпись для поля "email" фильтров для управления повторной подачей заявлений: `Email`'),
            'campaign_ref_uid' => Yii::t('common/modules/abiturient/resubmission-search', 'Подпись для поля "campaign_ref_uid" фильтров для управления повторной подачей заявлений: `Приёмная кампания`'),
            'allow_resubmit' => Yii::t('common/modules/abiturient/resubmission-search', 'Подпись для поля "allow_resubmit" фильтров для управления повторной подачей заявлений: `Повторная подача разрешена`'),
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $restricted_type_ids = ApplicationType::find()
            ->select([ApplicationType::tableName() . '.id'])
            ->joinWith(['applicationTypeSettings application_type_settings'])
            ->andWhere(['application_type_settings.name' => 'allow_secondary_apply_after_approval', 'application_type_settings.value' => [null, 0]]);

        $query = (new Query())
            ->select([
                'u.id user_id',
                'type.id type_id',
                'u.email email',
                'fio.user_fio fio',
                'campaign_ref.reference_uid campaign_uid',
                'type.name campaign_name',
                '(CASE WHEN resubmitPermission.allow = TRUE THEN TRUE ELSE FALSE END) allow_resubmit',
            ])
            ->distinct()
            ->from(['u' => User::tableName()])
            ->join('INNER JOIN', ['app' => BachelorApplication::tableName()], 'app.user_id = u.id')
            ->join('INNER JOIN', ['type' => ApplicationType::tableName()], 'type.id = app.type_id')
            ->join('INNER JOIN', ['campaign' => AdmissionCampaign::tableName()], 'campaign.id = type.campaign_id')
            ->join('INNER JOIN', ['campaign_ref' => StoredAdmissionCampaignReferenceType::tableName()], 'campaign_ref.id = campaign.ref_id')
            ->join('LEFT JOIN', ['resubmitPermission' => ApplicationResubmitPermission::tableName()], 'resubmitPermission.user_id = u.id AND resubmitPermission.type_id = type.id')
            ->andWhere(['type.id' => $restricted_type_ids])
            ->andWhere(['app.draft_status' => IDraftable::DRAFT_STATUS_APPROVED])
            ->andWhere(['app.status' => ApplicationInterface::STATUS_APPROVED]);

        $fioTable = (new Query())
            ->select([
                'user_id AS id',
                "CONCAT(lastname, ' ', firstname, ' ', middlename) AS user_fio"
            ])
            ->from(UserProfile::tableName());
        $query->leftJoin(
            ['fio' => $fioTable],
            'fio.id = app.user_id'
        );

        $this->load($params);
        if (empty($this->pageSize)) {
            $this->pageSize = 20;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => (int)$this->pageSize,
                'route' => 'resubmission/manage',
            ],
            'sort' => [
                'attributes' => [
                    'email' => [
                        'asc' => ['email' => SORT_ASC],
                        'desc' => ['email' => SORT_DESC]
                    ],
                    'fio' => [
                        'asc' => ['fio' => SORT_ASC],
                        'desc' => ['fio' => SORT_DESC]
                    ],
                    'campaign_name' => [
                        'asc' => ['campaign_name' => SORT_ASC],
                        'desc' => ['campaign_name' => SORT_DESC]
                    ],
                    'allow_resubmit' => [
                        'asc' => ['allow_resubmit' => SORT_ASC],
                        'desc' => ['allow_resubmit' => SORT_DESC]
                    ],
                ]
            ]
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        
        if ($this->isNotEmptyString($this->fio)) {
            

            $query->andWhere([LikeQueryManager::getActionName(), 'fio.user_fio', trim((string)$this->fio)]);
        }

        $query->andFilterWhere([
            LikeQueryManager::getActionName(), 'email', $this->email
        ]);

        
        if ($this->isNotEmptyString($this->campaign_ref_uid) || $this->campaign_ref_uid === '-1') {
            $query
                ->andWhere(['campaign_ref.reference_uid' => $this->campaign_ref_uid]);
        }

        if ($this->allow_resubmit !== null && $this->allow_resubmit !== '') {
            $query->andWhere(['(CASE WHEN resubmitPermission.allow = TRUE THEN TRUE ELSE FALSE END)' => BooleanCaster::cast($this->allow_resubmit)]);
        }

        return $dataProvider;
    }

    private function isNotEmptyString(?string $str): bool
    {
        return !EmptyCheck::isEmpty($str);
    }
}
