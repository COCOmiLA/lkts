<?php

namespace common\models\notification;

use common\components\LikeQueryManager;
use common\models\EmptyCheck;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ReceiverSearch extends User
{
    public $pageSize;

    public $fio = '';
    public $campaign_code = '';
    public $has_entrant_tests = '';
    public $has_preferences = '';
    public $has_target_receptions = '';
    public $has_full_cost_recovery = '';
    public $application_status = '';

    public function rules()
    {
        return [
            [['pageSize'], 'integer'],
            [
                [
                    'email',
                    'fio',
                    'campaign_code',
                    'has_entrant_tests',
                    'has_preferences',
                    'has_target_receptions',
                    'has_full_cost_recovery',
                    'application_status',
                ],
                'safe'
            ],
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'fio' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "fio" формы "Поиск получателя": `ФИО`'),
            'campaign_code' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "campaign_code" формы "Поиск получателя": `Приёмная кампания`'),
            'has_entrant_tests' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_entrant_tests" формы "Поиск получателя": `Наличие экзаменов ВИ`'),
            'has_preferences' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_preferences" формы "Поиск получателя": `Наличие льгот`'),
            'has_target_receptions' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_target_receptions" формы "Поиск получателя": `Наличие целевых договоров`'),
            'has_full_cost_recovery' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "has_full_cost_recovery" формы "Поиск получателя": `Наличие направлений с полным возмещением затрат`'),
            'application_status' => Yii::t('common/models/notification/receiver-search', 'Подпись для поля "application_status" формы "Поиск получателя": `Статус заявления`'),
        ]);
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = User::find()->distinct();
        $table_name = User::tableName();

        $allow_roles = [
            static::ROLE_ABITURIENT
        ];
        $query->leftJoin('{{%rbac_auth_assignment}}', "rbac_auth_assignment.user_id = {$table_name}.id");
        $query->andWhere(['rbac_auth_assignment.item_name' => $allow_roles]);

        $this->load($params);

        if (empty($this->pageSize)) {
            $this->pageSize = 20;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => (int)$this->pageSize,
                'route' => 'notification/index',
            ]
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            LikeQueryManager::getActionName(), 'email', $this->email
        ]);

        
        if ($this->isNotEmptyString($this->fio)) {
            $fioTable = (new Query())
                ->select([
                    'user_id AS id',
                    "CONCAT(lastname, ' ', firstname, ' ', middlename) AS user_fio"
                ])
                ->from('user_profile');

            $query->leftJoin(
                ['fio' => $fioTable],
                "fio.id = {$table_name}.id"
            )->andWhere([LikeQueryManager::getActionName(), 'user_fio', trim((string)$this->fio)]);
        }

        
        if ($this->isNotEmptyString($this->campaign_code) || $this->campaign_code === '-1') {
            $query
                ->joinWith('applications.type.campaign.referenceType campaign_ref')
                ->andWhere(['campaign_ref.reference_uid' => $this->campaign_code]);
        }

        
        if ($this->isNotEmptyString($this->has_entrant_tests)) {
            $this->addEntranceTestFilter($query);
        }

        
        if ($this->isNotEmptyString($this->has_preferences)) {
            $this->addPreferenceFilter($query);
        }

        
        if ($this->isNotEmptyString($this->has_target_receptions)) {
            $this->addTargetReceptionFilter($query);
        }

        
        if ($this->isNotEmptyString($this->has_full_cost_recovery)) {
            $this->addFullCostFilter($query);
        }

        
        if ($this->isNotEmptyString($this->application_status)) {
            $this->addApplicationStatusFilter($query);
        }

        return $dataProvider;
    }

    protected function addEntranceTestFilter(ActiveQuery $query)
    {
        $table = User::find()->select(User::tableName() . '.id')
            ->innerJoinWith(['applications' => function ($q) {
                $q->innerJoinWith('egeResults', false);
            }], false);

        $operator = 'not in';
        if ($this->has_entrant_tests == '1') {
            $operator = 'in';
        }

        $query->andWhere([
            $operator,
            User::tableName() . '.id',
            $table
        ]);
    }

    protected function addPreferenceFilter(ActiveQuery $query)
    {
        $table = User::find()->select(User::tableName() . '.id')
            ->innerJoinWith(['applications' => function ($q) {
                $q->innerJoinWith('preferences', false);
            }], false);

        $operator = 'not in';
        if ($this->has_preferences == '1') {
            $operator = 'in';
        }

        $query->andWhere([
            $operator,
            User::tableName() . '.id',
            $table
        ]);
    }

    protected function addTargetReceptionFilter(ActiveQuery $query)
    {
        $table = User::find()->select(User::tableName() . '.id')
            ->innerJoinWith(['applications' => function ($q) {
                $q->innerJoinWith('bachelorTargetReceptions', false);
            }], false);

        $operator = 'not in';
        if ($this->has_target_receptions == '1') {
            $operator = 'in';
        }

        $query->andWhere([
            $operator,
            User::tableName() . '.id',
            $table
        ]);
    }

    protected function addApplicationStatusFilter(ActiveQuery $query)
    {
        $tnUser = User::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();
        $application = BachelorApplication::find()
            ->select([BachelorApplication::tableName() . '.user_id'])
            ->active()
            ->andWhere(["{$tnBachelorApplication}.status" => $this->application_status]);

        $query->andWhere(['IN', "{$tnUser}.id", $application]);
    }

    protected function addFullCostFilter(ActiveQuery $query)
    {
        $table = BachelorApplication::find()
            ->select([BachelorApplication::tableName() . '.user_id'])
            ->joinWith(['allBachelorSpecialities' => function ($q) {
                $q->joinWith(['speciality.educationSourceRef education_source_ref'], false);
            }], false)
            ->active()
            ->andWhere(['education_source_ref.reference_uid' => \Yii::$app->configurationManager->getCode('full_cost_recovery_guid')]);

        $operator = 'not in';
        if ($this->has_full_cost_recovery == '1') {
            $operator = 'in';
        }

        $query->andWhere([
            $operator,
            User::tableName() . '.id',
            $table
        ]);
    }

    private function isNotEmptyString(?string $str): bool
    {
        return !EmptyCheck::isEmpty($str);
    }

    


    public static function getApplicationStatusesData(): array
    {
        return BachelorApplication::sandboxMessages();
    }
}
