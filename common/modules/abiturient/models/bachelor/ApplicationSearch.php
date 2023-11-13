<?php

namespace common\modules\abiturient\models\bachelor;

use backend\models\FiltersSetting;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\LikeQueryManager;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\EmptyCheck;
use common\models\query\ActiveRecordDataProvider;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\PersonalData;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;

class ApplicationSearch extends BachelorApplication
{
    public function rules()
    {
        return [
            [
                [
                    'fio',
                    'guid',
                    'columns',
                    'filters',
                    'sent_at',
                    'birthday',
                    'pageSize',
                    'usermail',
                    'agreement',
                    'exam_form',
                    'created_at',
                    'to_sent_at',
                    'citizenship',
                    'preferences',
                    'to_birthday',
                    'campaign_code',
                    'educationForm',
                    'to_created_at',
                    'historyChanges',
                    'specialityName',
                    'targetReception',
                    'review_agreement',
                    'applicantsComment',
                    'moderatorsComment',
                    'last_management_at',
                    'to_last_management_at',
                    'hasIndividualAchievement',
                    'financial_basis',
                    'education_level',
                    'admission_base',
                    'admission_category',
                    'phone_number',
                    'snils',
                ],
                'safe'
            ],
            [
                [
                    'status',
                    'statusBlock'
                ],
                'integer'
            ],
        ];
    }

    public $fio = '';
    public $guid = '';
    public $status = '';
    public $birthday = '';
    public $pageSize = '';
    public $usermail = '';
    public $agreement = '';
    public $exam_form = '';
    public $citizenship = '';
    public $preferences = '';
    public $statusBlock = '';
    public $to_birthday = '';
    public $campaign_code = '';
    public $educationForm = '';
    public $historyChanges = '';
    public $specialityName = '';
    public $lastManagerName = '';
    public $targetReception = '';
    public $review_agreement = '';
    public $applicantsComment = '';
    public $moderatorsComment = '';
    public $hasIndividualAchievement = '';

    public $sent_at = '';
    public $created_at = '';
    public $to_sent_at = '';
    public $to_created_at = '';
    public $last_management_at = '';
    public $to_last_management_at = '';
    public $financial_basis = '';
    public $education_level = '';
    public $admission_base = '';
    public $admission_category = '';
    public $phone_number = '';
    public $snils = '';

    public $columns = [];
    public $filters = [];

    public $filterLoaded = false; 

    public function __construct($config = [])
    {
        parent::__construct($config);

        $settings = FiltersSetting::find()
            ->where([
                'or',
                ['show_column' => FiltersSetting::ENABLE],
                ['show_filter' => FiltersSetting::ENABLE],
            ])
            ->orderBy('serial')
            ->all();

        if (!empty($settings)) {
            $this->columns = array_filter(
                $settings,
                function ($filter) {
                    
                    return $filter->show_column == FiltersSetting::ENABLE;
                }
            );

            $this->filters = array_filter(
                $settings,
                function ($filter) {
                    
                    return $filter->show_filter == FiltersSetting::ENABLE;
                }
            );
        }
    }


    


    private static $joinedTables = [];

    




    private static function convertDataFromLkToDataSql(string $dataFromLk): string
    {
        return date('Y-m-d', strtotime($dataFromLk));
    }

    






    private function joinTableToMainQuery(ActiveQuery $query, $joinType, $tableToJoin, $on): ActiveQuery
    {
        $table = '';
        $tableAlias = '';

        if (is_string($tableToJoin)) {
            $table = $tableToJoin;
            $tableAlias = $tableToJoin;
        }

        if (is_array($tableToJoin)) {
            $tableAlias = array_keys($tableToJoin)[0];
            $table = $tableToJoin[$tableAlias];
        }

        if (array_key_exists($tableAlias, self::$joinedTables)) {
            return $query;
        }

        $query->join($joinType, $tableToJoin, $on);
        self::$joinedTables[$tableAlias] = $table;
        return $query;
    }

    public function search($params, $type, $campaignRefUid)
    {
        $this->load($params);
        $bachelorApplicationArchiveFlag = false;

        switch ($type) {
            case ('all'):
                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.draft_status' => [IDraftable::DRAFT_STATUS_SENT, IDraftable::DRAFT_STATUS_APPROVED]])
                    ->andFilterWhere(['bachelor_application.status' => $this->status]);
                break;

            case ('moderate'):
                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.draft_status' => IDraftable::DRAFT_STATUS_SENT])
                    ->andWhere(['in', 'bachelor_application.status', [
                        BachelorApplication::STATUS_SENT,
                        BachelorApplication::STATUS_REJECTED_BY1C,
                        BachelorApplication::STATUS_SENT_AFTER_APPROVED,
                        BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED,
                    ]])
                    ->andFilterWhere(['bachelor_application.status' => $this->status]);
                break;

            case ('approved'):
            case ('enlisted'):
                $tnBachelorSpeciality = BachelorSpeciality::tableName();
                $tnBachelorApplication = BachelorApplication::tableName();
                $query = BachelorApplication::find()
                    ->joinWith('specialitiesWithoutOrdering')
                    ->andWhere(["{$tnBachelorApplication}.status" => BachelorApplication::STATUS_APPROVED])
                    ->andWhere(["{$tnBachelorApplication}.draft_status" => IDraftable::DRAFT_STATUS_APPROVED]);

                $subQuery = $query;
                if ($type == 'approved') {
                    $subQuery = (clone $query);
                }
                $subQuery->andWhere(["{$tnBachelorSpeciality}.is_enlisted" => true]);

                if ($type == 'approved') {
                    $query->andWhere(['NOT IN', "{$tnBachelorApplication}.id", $subQuery->select("{$tnBachelorApplication}.id")]);
                } else {
                    $query = $subQuery;
                }

                break;

            case ('want-delete'):
                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.draft_status' => IDraftable::DRAFT_STATUS_SENT])
                    ->andWhere(['in', 'bachelor_application.status', [
                        BachelorApplication::STATUS_WANTS_TO_BE_REMOTE,
                        BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
                    ]]);
                break;

            case ('deleted'):
                $bachelorApplicationArchiveFlag = true;

                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.archive_reason' => DraftsManager::REASON_RETURN])
                    ->andWhere([
                        'OR',
                        ['bachelor_application.status' => BachelorApplication::STATUS_WANTS_TO_BE_REMOTE],
                        ['bachelor_application.status' => BachelorApplication::STATUS_WANTS_TO_RETURN_ALL],
                    ]);
                break;

            case ('declined'):
                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.draft_status' => IDraftable::DRAFT_STATUS_SENT])
                    ->andWhere(['bachelor_application.status' => BachelorApplication::STATUS_NOT_APPROVED]);
                break;

            case ('preparing'):
                $draftStatusCreated = IDraftable::DRAFT_STATUS_CREATED;
                $tnBachelorApplicationExceptCreated = '{{%bachelor_application_except_created}}';
                $tnBachelorApplication = BachelorApplication::tableName();
                $joinByQuery = new Expression("
                    $tnBachelorApplication.user_id = $tnBachelorApplicationExceptCreated.user_id AND
                    {$tnBachelorApplicationExceptCreated}.draft_status != $draftStatusCreated
                ");
                $query = BachelorApplication::find()
                    ->leftJoin([$tnBachelorApplicationExceptCreated => $tnBachelorApplication], $joinByQuery)
                    ->andWhere(["{$tnBachelorApplication}.draft_status" => IDraftable::DRAFT_STATUS_CREATED])
                    ->andWhere(['IS', "{$tnBachelorApplicationExceptCreated}.user_id", null]);
                break;

            case ('enrollment-rejection'):
                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.draft_status' => IDraftable::DRAFT_STATUS_SENT])
                    ->andWhere(['bachelor_application.status' => BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED]);
                break;

            default:
                $query = BachelorApplication::find()
                    ->andWhere(['bachelor_application.draft_status' => IDraftable::DRAFT_STATUS_SENT]);
                break;
        }

        $query->andWhere(['bachelor_application.archive' => $bachelorApplicationArchiveFlag]);

        
        $query->leftJoin(
            ['moderating_app' => BachelorApplication::tableName()],
            'bachelor_application.user_id = moderating_app.user_id 
            AND bachelor_application.type_id = moderating_app.type_id 
            AND moderating_app.archive = FALSE
            AND moderating_app.draft_status = :moderating_draft_status',
            [
                ':moderating_draft_status' => IDraftable::DRAFT_STATUS_MODERATING
            ]
        );

        
        $query->joinWith(['linkedAbiturientQuestionary.personalData.citizenship linked_citizenship']);
        $query->joinWith(['user']);

        $actual_questionaries = User::find()->joinWith(['actualAbiturientQuestionary.personalData.citizenship citizenship'])
            ->select([
                AbiturientQuestionary::tableName() . '.user_id user_id',
                'citizenship.id citizenship_id',
                'citizenship.name citizenship_name',
                'personal_data.lastname actual_lastname',
                'personal_data.main_phone actual_phone',
                'personal_data.snils actual_snils',
            ]);
        $query->leftJoin(['actual_questionary' => $actual_questionaries], 'actual_questionary.user_id = bachelor_application.user_id');

        $query->leftJoin(User::tableName() . ' last_manager', 'bachelor_application.last_manager_id = last_manager.id');
        $query->joinWith(['type.campaign']);


        $this->filterLoaded = $this->load($params); 

        
        if ($this->filterLoaded) {
            $this->saveToSession();
        } else {
            $this->loadFromSession();
        }

        $this->lastManagerName = ArrayHelper::getValue($params, 'ApplicationSearch.lastManagerName');

        
        if ($this->isNotEmptyString($this->targetReception)) {
            $table = (new Query())
                ->select(['btar.id_application'])
                ->from(['ba_tar' => 'bachelor_application'])
                ->leftJoin(
                    ['btar' => 'bachelor_target_reception'],
                    'ba_tar.id = btar.id_application'
                )
                ->where(['btar.archive' => false]);

            $operator = 'not in';
            if ($this->targetReception == '1') {
                $operator = 'in';
            }

            $query->andWhere([
                $operator,
                'bachelor_application.id',
                $table
            ]);
        }

        
        if ($this->isNotEmptyString($this->preferences)) {
            $table = (new Query())
                ->select(['bpref.id_application'])
                ->from(['ba_spec' => 'bachelor_application'])
                ->leftJoin(
                    ['bpref' => 'bachelor_preferences'],
                    'ba_spec.id = bpref.id_application'
                )
                ->where(['bpref.archive' => false])
                ->andWhere(['is not', 'bpref.privilege_id', null]);

            $operator = 'not in';
            if ($this->preferences == '1') {
                $operator = 'in';
            }

            $query->andWhere([
                $operator,
                'bachelor_application.id',
                $table
            ]);
        }

        
        if ($this->isNotEmptyString($this->statusBlock)) {
            $query->andFilterWhere([
                'or',
                [
                    'bachelor_application.block_status' => $this->statusBlock
                ],
                [
                    'moderating_app.block_status' => $this->statusBlock
                ],
            ]);
        }


        
        if (isset($this->historyChanges) && is_array($this->historyChanges)) {
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['app_history' => 'application_history'], 'bachelor_application.id = app_history.application_id')
                ->andWhere(['in', 'app_history.type', $this->historyChanges]);
        }

        
        if (isset($this->citizenship) && is_array($this->citizenship)) {
            $this->citizenship = array_filter(
                $this->citizenship,
                function ($item) {
                    return !EmptyCheck::isEmpty($item);
                }
            );
        }
        if (!EmptyCheck::isEmpty($this->citizenship) && is_array($this->citizenship)) {
            $query->andWhere([
                'or',
                ['IN', 'linked_citizenship.id', $this->citizenship],
                ['IN', 'actual_questionary.citizenship_id', $this->citizenship],
            ]);
        }

        
        if ($this->isNotEmptyString($this->usermail)) {
            $query->andWhere([LikeQueryManager::getActionName(), 'user.email', trim((string)$this->usermail)]);
        }

        
        if ($this->isNotEmptyString($this->educationForm)) {
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['bs' => 'bachelor_speciality'], 'bachelor_application.id = bs.application_id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['dic_spec' => 'dictionary_speciality'], 'bs.speciality_id = dic_spec.id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['eduFormRef' => 'education_form_reference_type'], 'eduFormRef.id = dic_spec.education_form_ref_id')
                ->andWhere(['eduFormRef.reference_uid' => $this->educationForm]);
        }

        
        if (isset($this->specialityName) && is_array($this->specialityName)) {
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['bs' => 'bachelor_speciality'], 'bachelor_application.id = bs.application_id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['dic_spec' => 'dictionary_speciality'], 'bs.speciality_id = dic_spec.id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['directionRef' => 'direction_reference_type'], 'directionRef.id = dic_spec.direction_ref_id')
                ->andWhere(['in', 'directionRef.reference_uid', $this->specialityName]);
        }

        
        if ($this->isNotEmptyString($this->hasIndividualAchievement)) {
            $iaTable = (new Query())
                ->select(['ba_ind.id as app_id'])
                ->from(['ba_ind' => 'bachelor_application'])
                ->leftJoin(
                    'application_type',
                    'ba_ind.type_id = application_type.id'
                )
                ->leftJoin(
                    'admission_campaign',
                    'application_type.campaign_id = admission_campaign.id'
                )
                ->leftJoin(
                    'dictionary_individual_achievement',
                    'admission_campaign.ref_id = dictionary_individual_achievement.campaign_ref_id'
                )
                ->leftJoin(
                    'individual_achievement',
                    'dictionary_individual_achievement.id = individual_achievement.dictionary_individual_achievement_id'
                )
                ->where('ba_ind.user_id = individual_achievement.user_id')
                ->andWhere(['dictionary_individual_achievement.archive' => false]);

            $operator = 'not in';
            if ($this->hasIndividualAchievement == '1') {
                $operator = 'in';
            }

            $query->andWhere([
                $operator,
                'bachelor_application.id',
                $iaTable
            ]);
        }

        
        if (
            $this->isNotEmptyString($this->to_sent_at) &&
            $this->isNotEmptyString($this->sent_at)
        ) {
            $query->joinWith('user')
                ->andWhere([
                    'between',
                    'bachelor_application.sent_at',
                    strtotime("{$this->sent_at} 00:00"),
                    strtotime("{$this->to_sent_at} 23:59"),
                ]);
        }

        
        if (
            $this->isNotEmptyString($this->to_created_at) &&
            $this->isNotEmptyString($this->created_at)
        ) {
            $query->joinWith('user')
                ->andWhere([
                    'between',
                    'bachelor_application.created_at',
                    strtotime("{$this->created_at} 00:00"),
                    strtotime("{$this->to_created_at} 23:59"),
                ]);
        }

        
        if (
            $this->isNotEmptyString($this->to_last_management_at) &&
            $this->isNotEmptyString($this->last_management_at)
        ) {
            $query->andWhere([
                'between',
                'bachelor_application.last_management_at',
                strtotime("{$this->last_management_at} 00:00"),
                strtotime("{$this->to_last_management_at} 23:59"),
            ]);
        }

        
        if ($this->isNotEmptyString($this->guid)) {
            $query->joinWith('user')
                ->andWhere([LikeQueryManager::getActionName(), 'user.guid', trim((string)$this->guid)]);
        }

        
        if ($this->isNotEmptyString($this->fio)) {
            
            $fioTable = (new Query())
                ->select([
                    'user_id AS id',
                    "CONCAT(lastname, ' ', firstname, ' ', middlename) AS user_fio"
                ])
                ->from('user_profile');

            $query->leftJoin(
                ['fio' => $fioTable],
                'fio.id = bachelor_application.user_id'
            )
                ->andWhere([LikeQueryManager::getActionName(), 'user_fio', trim((string)$this->fio)]);
        }

        
        if (!$this->isNotEmptyString($this->campaign_code) || $this->campaign_code === '-1') {
            if ($campaignRefUid) {
                $query->joinWith('type')
                    ->joinWith('type.campaign.referenceType campaign_ref')
                    ->andWhere(['campaign_ref.reference_uid' => $campaignRefUid]);
            }
        } else {
            $query->joinWith('type')
                ->joinWith('type.campaign.referenceType campaign_ref')
                ->andWhere(['campaign_ref.reference_uid' => $this->campaign_code]);
        }

        
        if (isset($this->lastManagerName) && is_array($this->lastManagerName)) {
            $query->andWhere(['in', 'bachelor_application.last_manager_id', $this->lastManagerName]);
        }

        
        if ($this->isNotEmptyString($this->exam_form)) {
            $tn = EgeResult::tableName();
            $query->innerJoinWith([
                'allEgeResults' => function ($q) {
                    $q->joinWith('cgetExamForm cget_exam_form');
                }
            ])->andWhere(["cget_exam_form.reference_uid" => $this->exam_form]);
        }

        
        if (
            $this->isNotEmptyString($this->to_birthday) &&
            $this->isNotEmptyString($this->birthday)
        ) {
            $tn = PersonalData::tableName();
            $query->joinWith('abiturientQuestionary.personalData')
                ->andWhere([
                    'between',
                    IndependentQueryManager::strToDate("{$tn}.birthdate"),
                    ApplicationSearch::convertDataFromLkToDataSql($this->birthday),
                    ApplicationSearch::convertDataFromLkToDataSql($this->to_birthday),
                ]);
        }

        
        if ($this->isNotEmptyString($this->applicantsComment)) {
            $query->joinWith('commentsComing')
                ->andWhere([LikeQueryManager::getActionName(), 'comment', $this->applicantsComment]);
        }

        
        if ($this->isNotEmptyString($this->moderatorsComment)) {
            $tn = BachelorApplication::tableName();
            $query->andWhere([LikeQueryManager::getActionName(), "{$tn}.moderator_comment", $this->moderatorsComment]);
        }

        
        if ($this->isNotEmptyString($this->agreement)) {
            if ($this->agreement) {
                $query = BachelorApplication::hasAdmissionAgreementQuery($query);
            } else {
                $query = BachelorApplication::doesNotHaveAdmissionAgreementQuery($query);
            }
        }

        
        if ($this->isNotEmptyString($this->review_agreement)) {
            if ($this->review_agreement) {
                $query = BachelorApplication::hasAgreementDeclineQuery($query);
            } else {
                $query = BachelorApplication::doesNotHasAgreementDeclineQuery($query);
            }
        }

        
        if ($this->isNotEmptyString($this->financial_basis)) {
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['bs' => 'bachelor_speciality'], 'bachelor_application.id = bs.application_id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['dic_spec' => 'dictionary_speciality'], 'bs.speciality_id = dic_spec.id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['education_source_ref' => 'education_source_reference_type'], 'education_source_ref.id = dic_spec.education_source_ref_id')
                ->andWhere(['education_source_ref.reference_uid' => $this->financial_basis]);
        }

        
        if ($this->isNotEmptyString($this->education_level)) {
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['bs' => 'bachelor_speciality'], 'bachelor_application.id = bs.application_id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['dic_spec' => 'dictionary_speciality'], 'bs.speciality_id = dic_spec.id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['education_level_ref' => 'education_level_reference_type'], 'education_level_ref.id = dic_spec.education_level_ref_id')
                ->andWhere(['education_level_ref.reference_uid' => $this->education_level]);
        }

        
        if ($this->isNotEmptyString($this->admission_category)) {
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['bs' => 'bachelor_speciality'], 'bachelor_application.id = bs.application_id');
            $query = $this
                ->joinTableToMainQuery($query, 'LEFT JOIN', ['admission_category' => 'dictionary_admission_categories'], 'bs.admission_category_id = admission_category.id')
                ->andWhere(['admission_category.ref_key' => $this->admission_category]);
        }

        
        if ($this->isNotEmptyString($this->phone_number)) {
            $query->andWhere([
                'or',
                IndependentQueryManager::searchOnlyByDigitsCondition('personal_data.main_phone', $this->phone_number),
                IndependentQueryManager::searchOnlyByDigitsCondition('actual_phone', $this->phone_number),
            ]);
        }

        
        if ($this->isNotEmptyString($this->snils) && !preg_match('/___-___-___ __/', $this->snils)) {
            $trimmed_snils = rtrim($this->snils, '_ ');
            $query->andWhere([
                'or',
                [LikeQueryManager::getActionName(), 'personal_data.snils', $trimmed_snils],
                [LikeQueryManager::getActionName(), 'actual_snils', $trimmed_snils],
            ]);
        }

        if (empty($this->pageSize)) {
            $this->pageSize = 20;
        }
        return new ActiveRecordDataProvider([
            'query' => $query,
            'primary_column' => 'bachelor_application.id',
            'pagination' => [
                'pagesize' => (int)$this->pageSize,
            ],
            'sort' => [
                'defaultOrder' => ['sent_at' => SORT_ASC],
                'attributes' => [
                    'id',
                    'status' => [
                        'asc' => ['bachelor_application.status' => SORT_ASC],
                        'desc' => ['bachelor_application.status' => SORT_DESC]
                    ],
                    'sent_at' => [
                        'asc' => ['bachelor_application.sent_at' => SORT_ASC],
                        'desc' => ['bachelor_application.sent_at' => SORT_DESC]
                    ],
                    'created_at' => [
                        'asc' => ['bachelor_application.created_at' => SORT_ASC],
                        'desc' => ['bachelor_application.created_at' => SORT_DESC]
                    ],
                    'last_management_at' => [
                        'asc' => ['bachelor_application.last_management_at' => SORT_ASC],
                        'desc' => ['bachelor_application.last_management_at' => SORT_DESC]
                    ],
                    'fio' => [
                        'asc' => ['(CASE WHEN personal_data.lastname IS NULL THEN actual_questionary.actual_lastname ELSE personal_data.lastname END)' => SORT_ASC],
                        'desc' => ['(CASE WHEN personal_data.lastname IS NULL THEN actual_questionary.actual_lastname ELSE personal_data.lastname END)' => SORT_DESC],
                    ],
                    'usermail' => [
                        'asc' => ['user.email' => SORT_ASC],
                        'desc' => ['user.email' => SORT_DESC],
                    ],
                    'guid' => [
                        'asc' => ['user.guid' => SORT_ASC],
                        'desc' => ['user.guid' => SORT_DESC],
                    ],
                    'lastManagerName' => [
                        'asc' => ['lastManager.username' => SORT_ASC],
                        'desc' => ['lastManager.username' => SORT_DESC]
                    ],
                    'type.campaign.name' => [
                        'asc' => ['admission_campaign.name' => SORT_ASC],
                        'desc' => ['admission_campaign.name' => SORT_DESC]
                    ],
                    'citizenship' => [
                        'asc' => ['linked_citizenship.name' => SORT_ASC, 'actual_questionary.citizenship_name' => SORT_ASC],
                        'desc' => ['linked_citizenship.name' => SORT_DESC, 'actual_questionary.citizenship_name' => SORT_ASC]
                    ],
                ]
            ]
        ]);
    }

    




    private function isNotEmptyString($str = '')
    {
        return !EmptyCheck::isEmpty($str);
    }

    



    public function getColumnsLayout($type)
    {
        $columns = [];
        $statusBlockColumn = []; 
        foreach ($this->columns as $column) {
            switch ($column->name) {
                case 'statusBlock':
                    $statusBlockColumn[] = [
                        'header' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'statusBlock',
                        'format' => 'raw',
                        'value' => function (BachelorApplication $model) {
                            $message = '';
                            $messageTime = '';
                            $blockerName = $model->getBlockerName();
                            [$blocked, $time_to_wait] = $model->isApplicationBlocked();
                            $moderating_app = $model->getModeratingApplication();
                            if ($moderating_app && $moderating_app->id != $model->id) {
                                [$blocked, $time_to_wait] = $moderating_app->isApplicationBlocked();
                                $blockerName = $moderating_app->getBlockerName();
                            }
                            if ($blockerName) {
                                
                                
                                if ($blocked && $time_to_wait > 0) {
                                    $messageTime = Yii::t(
                                        'sandbox/index/filter-block',
                                        'Текст сообщения, оповещающего о том сколько осталось до окончания блокировки; на стр. поданных заявлений: `Время до разблокировки: {date}`',
                                        ['date' => date('i:s', $time_to_wait)]
                                    );
                                }
                                $title = Yii::t(
                                    'sandbox/index/filter-block',
                                    'Текст всплывающей подсказки на заблокированном заявлении; на стр. поданных заявлений: `Заблокировал: {blockerName} {messageTime}`',
                                    [
                                        'blockerName' => $blockerName,
                                        'messageTime' => $messageTime,
                                    ]
                                );
                                $message = "<i title=\"{$title}\" class=\"fa fa-lock\"></i>";
                            }
                            return $message;
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'fio':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'fio'
                    ];
                    break;

                case 'usermail':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'usermail',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'guid':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'guid',
                        'value' => 'user.guid',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'campaign_code':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'class' => 'col-2',
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'type.campaign.name',
                        'value' => 'type.campaign.referenceType.reference_id',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'citizenship':
                    $columns[] = [
                        'attribute' => 'citizenship',
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => 'abiturientQuestionary.personalData.citizenship.name',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'hasIndividualAchievement':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            $hasIa = (new Query())
                                ->from(['ba_has_ind' => 'bachelor_application'])
                                ->leftJoin(
                                    'application_type',
                                    'ba_has_ind.type_id = application_type.id'
                                )
                                ->leftJoin(
                                    'admission_campaign',
                                    'application_type.campaign_id = admission_campaign.id'
                                )
                                ->leftJoin(
                                    'dictionary_individual_achievement',
                                    'admission_campaign.ref_id = dictionary_individual_achievement.campaign_ref_id'
                                )
                                ->leftJoin(
                                    'admission_campaign_reference_type',
                                    'dictionary_individual_achievement.campaign_ref_id = admission_campaign_reference_type.id'
                                )
                                ->leftJoin(
                                    'individual_achievement',
                                    'dictionary_individual_achievement.id = individual_achievement.dictionary_individual_achievement_id'
                                )
                                ->where('ba_has_ind.user_id = individual_achievement.user_id')
                                ->andWhere(['dictionary_individual_achievement.archive' => false])
                                ->andWhere(['individual_achievement.user_id' => $model->user_id])
                                ->andWhere(['admission_campaign_reference_type.reference_uid' => $model->type->rawCampaign->referenceType->reference_uid])
                                ->exists();

                            if ($hasIa) {
                                return Yii::t(
                                    'sandbox/index/filter-block',
                                    'Текст для значения "Есть" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Есть`'
                                );
                            }
                            return Yii::t(
                                'sandbox/index/filter-block',
                                'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Нет`'
                            );
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'targetReception':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            $has = (new Query())
                                ->from('bachelor_application')
                                ->leftJoin(
                                    'bachelor_target_reception',
                                    'bachelor_application.id = bachelor_target_reception.id_application'
                                )
                                ->where(['bachelor_target_reception.archive' => false])
                                ->andWhere(['bachelor_target_reception.id_application' => $model->id])
                                ->exists();

                            if ($has) {
                                return Yii::t(
                                    'sandbox/index/filter-block',
                                    'Текст для значения "Есть" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Есть`'
                                );
                            }
                            return Yii::t(
                                'sandbox/index/filter-block',
                                'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Нет`'
                            );
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'preferences':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            $has = (new Query())
                                ->from(['ba_pref' => 'bachelor_application'])
                                ->leftJoin(
                                    'bachelor_preferences',
                                    'ba_pref.id = bachelor_preferences.id_application'
                                )
                                ->where(['bachelor_preferences.archive' => false])
                                ->andWhere(['bachelor_preferences.id_application' => $model->id])
                                ->andWhere(['is not', 'bachelor_preferences.privilege_id', null])
                                ->exists();

                            if ($has) {
                                return Yii::t(
                                    'sandbox/index/filter-block',
                                    'Текст для значения "Есть" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Есть`'
                                );
                            }
                            return Yii::t(
                                'sandbox/index/filter-block',
                                'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Нет`'
                            );
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'specialityName':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'specialitiesString',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'educationForm':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'attribute' => 'educationForm',
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            return static::glueValues($model->specialities, 'speciality.educationFormRef.reference_name');
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;

                case 'status':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => 'sandboxMessage',
                        'contentOptions' => ['class' => 'small_font width_10_percent word-breaker']
                    ];
                    break;

                case 'sent_at':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'sent_at',
                        'format' => ['date', 'php:d.m.Y H:i'],
                        'contentOptions' => ['class' => 'small_font'],
                    ];
                    break;

                case 'created_at':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:d.m.Y H:i'],
                        'contentOptions' => ['class' => 'small_font'],
                    ];
                    break;

                case 'last_management_at':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'last_management_at',
                        'format' => ['date', 'php:d.m.Y H:i'],
                        'contentOptions' => ['class' => 'small_font'],
                    ];
                    break;

                case 'lastManagerName':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => 'lastManagerName',
                    ];
                    break;

                case 'historyString':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => 'historyString',
                        'contentOptions' => ['class' => 'small_font width_20_percent'],
                    ];

                case 'exam_form':
                    $columns[] = [
                        'header' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            
                            $examForms = (new Query())
                                ->select('d.reference_name')
                                ->distinct()
                                ->from(['d' => StoredDisciplineFormReferenceType::tableName()])
                                ->innerJoin(['ege' => EgeResult::tableName()], 'ege.cget_exam_form_id = d.id')
                                ->where([
                                    'd.archive' => false,
                                    'ege.application_id' => $model->id,
                                ])
                                ->orderBy('d.reference_name')
                                ->column();

                            if (empty($examForms)) {
                                return Yii::t(
                                    'sandbox/index/filter-block',
                                    'Текст для значения "Нет" в выпадающем списке; блока с фильтрами на стр. поданных заявлений: `Нет`'
                                );
                            }

                            return implode(', ', $examForms);
                        },
                        'contentOptions' => ['class' => 'small_font'],
                    ];
                    break;

                case 'birthday':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'birthday',
                        'format' => ['date', 'php:d.m.Y'],
                        'contentOptions' => ['class' => 'small_font'],
                    ];
                    break;

                case 'applicantsComment':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'applicantsComment',
                        'value' => function ($model) {
                            return StringHelper::truncate((string)$model->applicantsComment, 70);
                        }
                    ];
                    break;

                case 'moderatorsComment':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'attribute' => 'moderator_comment',
                        'value' => function ($model) {
                            return StringHelper::truncate((string)$model->moderator_comment, 70);
                        }
                    ];
                    break;

                case 'agreement':
                case 'review_agreement':
                    $functionName = 'hasAdmissionAgreementQuery';
                    if ($column->name == 'review_agreement') {
                        $functionName = 'hasAgreementDeclineQuery';
                    }

                    $columns[] = [
                        'header' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) use ($functionName) {
                            

                            $tnBachelorApplication = BachelorApplication::tableName();
                            return BachelorApplication::{$functionName}(
                                BachelorApplication::find()
                                    ->andWhere(["{$tnBachelorApplication}.id" => $model->id])
                            )->exists() ? Yii::t(
                                'sandbox/index/all',
                                'Подпись наличия согласия на зачисление; в таблице с заявлениями на стр. поданных заявлений: `есть`'
                            ) : Yii::t(
                                'sandbox/index/all',
                                'Подпись отсутствия согласия на зачисление; в таблице с заявлениями на стр. поданных заявлений: `нет`'
                            );
                        }
                    ];
                    break;
                case 'financial_basis':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            return static::glueValues($model->specialities, 'speciality.educationSourceRef.reference_name');
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;
                case 'education_level':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            return static::glueValues($model->specialities, 'speciality.educationLevelRef.reference_name');
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;
                case 'admission_category':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => function ($model) {
                            return static::glueValues($model->specialities, 'admissionCategory.description');
                        },
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;
                case 'phone_number':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => 'abiturientQuestionary.personalData.main_phone',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;
                case 'snils':
                    $columns[] = [
                        'label' => Yii::t(
                            'abiturient/filter-table',
                            $column->label
                        ),
                        'headerOptions' => [
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                        ],
                        'value' => 'abiturientQuestionary.personalData.snils',
                        'contentOptions' => ['class' => 'small_font']
                    ];
                    break;
            }
        }

        $isViewer = Yii::$app->user->identity->isViewer();
        return array_merge(
            [['class' => SerialColumn::class]],
            $statusBlockColumn, 
            [[
                'class' => ActionColumn::class,
                'template' => $isViewer ? '{view}' : '{moderate}{view}',
                'buttons' => [
                    'moderate' => function ($url, $model) use ($type) {
                        
                        if (
                            in_array($type, ['moderate', 'want-delete', 'enrollment-rejection', 'all']) &&
                            $model->moderationAllowedByStatus()
                        ) {
                            if ($model->status == BachelorApplication::STATUS_WANTS_TO_RETURN_ALL) {
                                $iconClass = 'fa fa-trash';
                                $btnName = Yii::t(
                                    'sandbox/index/filter-block',
                                    'Подпись кнопки для подтверждения удаления заявления; блока с фильтрами на стр. поданных заявлений: `Подтвердить</br>отзыв`'
                                );
                            } elseif ($model->status == BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED) {
                                $iconClass = 'fa fa-ban';
                                $btnName = Yii::t(
                                    'sandbox/index/filter-block',
                                    'Подпись кнопки для подтверждения отказа от зачисления; блока с фильтрами на стр. поданных заявлений: `Подтвердить</br>отказ `'
                                );
                            } else {
                                $iconClass = 'fa fa-sign-in';
                                $btnName = Yii::t(
                                    'sandbox/index/filter-block',
                                    'Подпись кнопки для проверки заявления; блока с фильтрами на стр. поданных заявлений: `Проверить`'
                                );
                            }
                            return Html::a(
                                "<i class='{$iconClass}' aria-hidden='true'></i> {$btnName}",
                                $url,
                                ['class' => 'btn btn-outline-secondary']
                            );
                        }
                    },
                    'view' => function ($url, $model) use ($type, $isViewer) {
                        

                        if (
                            $isViewer ||
                            $type == 'deleted' ||
                            $type != 'moderate' &&
                            !$model->moderationAllowedByStatus()
                        ) {
                            $btnName = Yii::t(
                                'sandbox/index/filter-block',
                                'Подпись кнопки для просмотра заявления; блока с фильтрами на стр. поданных заявлений: `Посмотреть`'
                            );
                            return Html::a(
                                "<span class='fa fa-eye' aria-hidden='true'></span> {$btnName}",
                                $url,
                                ['class' => 'btn btn-outline-secondary']
                            );
                        }
                    }
                ],
                'contentOptions' => ['class' => 'small_font']
            ]],
            $columns
        );
    }

    private function saveToSession()
    {
        $data = [];
        foreach ($this->activeAttributes() as $attr) {
            $data[$attr] = $this->$attr;
        }
        Yii::$app->session->set('moderate_filters', $data);
    }

    private function loadFromSession()
    {
        $data = Yii::$app->session->get('moderate_filters');
        if (!$data) {
            return;
        }

        foreach ($data as $attr => $value) {
            if ($this->isNotEmptyString($value) || is_int($value)) {
                $this->$attr = $value;
                $this->filterLoaded = true;
            }
        }
    }

    protected static function glueValues(array $data, string $key): string
    {
        if (empty($data)) {
            return '';
        }

        $list = array_unique(
            array_map(
                function ($element) use ($key) {
                    return ArrayHelper::getValue($element, $key);
                },
                $data
            )
        );

        return implode(', ', $list);
    }
}
