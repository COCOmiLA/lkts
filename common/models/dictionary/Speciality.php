<?php

namespace common\models\dictionary;

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\queries\ArchiveQuery;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredBudgetLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDetailGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDirectionReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubdivisionReferenceType;
use common\models\dictionary\StoredReferenceType\StoredUGSReferenceType;
use common\models\errors\RecordNotFound;
use common\models\interfaces\IArchiveQueryable;
use common\models\ModelLinkedToReferenceType;
use common\models\ToAssocCaster;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use common\modules\abiturient\models\AdditionalReceiptDateControl;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\CampaignInfo;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;




















































class Speciality extends ModelLinkedToReferenceType implements IArchiveQueryable
{
    use ScenarioWithoutExistValidationTrait;

    protected static $refAdditionalClasses = [
        'competitive_group_ref_id' => StoredCompetitiveGroupReferenceType::class,
        'subdivision_ref_id' => StoredSubdivisionReferenceType::class,
        'direction_ref_id' => StoredDirectionReferenceType::class,
        'profile_ref_id' => StoredProfileReferenceType::class,
        'education_level_ref_id' => StoredEducationLevelReferenceType::class,
        'education_form_ref_id' => StoredEducationFormReferenceType::class,
        'education_program_ref_id' => EducationType::class,
        'education_source_ref_id' => StoredEducationSourceReferenceType::class,
        'budget_level_ref_id' => StoredBudgetLevelReferenceType::class,
        'detail_group_ref_id' => StoredDetailGroupReferenceType::class,
        'campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'curriculum_ref_id' => StoredCurriculumReferenceType::class,
        'ugs_ref_id' => StoredUGSReferenceType::class,
        'graduating_department_ref_id' => StoredSubdivisionReferenceType::class,
        'branch_ref_id' => StoredSubdivisionReferenceType::class,
        'parent_combined_competitive_group_ref_id' => StoredCompetitiveGroupReferenceType::class,
    ];

    protected static $refColumns = [
        'subdivision_ref_id' => 'SubdivisionRef',
        'competitive_group_ref_id' => 'CompetitiveGroupRef',
        'direction_ref_id' => 'DirectionRef',
        'profile_ref_id' => 'ProfileRef',
        'education_level_ref_id' => 'EducationLevelRef',
        'education_form_ref_id' => 'EducationFormRef',
        'education_program_ref_id' => 'EducationProgramRef',
        'education_source_ref_id' => 'EducationSourceRef',
        'budget_level_ref_id' => 'LevelBudgetRef',
        'detail_group_ref_id' => 'DetailGroupRef',
        'campaign_ref_id' => 'CampaignRef',
        'curriculum_ref_id' => 'CurriculumRef',
        'ugs_ref_id' => 'UGSRef',
        'graduating_department_ref_id' => 'GraduatingDepartmentRef',
        'branch_ref_id' => 'BranchRef',
        'parent_combined_competitive_group_ref_id' => 'ParentCombinedCompetitiveGroupRef',
    ];

    


    public static function tableName()
    {
        return '{{%dictionary_speciality}}';
    }

    


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'faculty_code',
                    'faculty_name',
                    'speciality_code',
                    'speciality_name',
                    'group_code',
                    'group_name'
                ],
                'required'
            ],
            [
                [
                    'faculty_code',
                    'speciality_code',
                    'profil_code',
                    'edulevel_code',
                    'eduform_code',
                    'eduprogram_code',
                    'finance_code',
                    'group_code',
                    'speciality_human_code',
                    'campaign_code',
                    'detail_group_code',
                    'budget_level_code',
                    'budget_level_name'
                ],
                'string',
                'max' => 100
            ],
            [
                [
                    'faculty_name',
                    'speciality_name',
                    'profil_name',
                    'edulevel_name',
                    'eduform_name',
                    'eduprogram_name',
                    'finance_name',
                    'group_name',
                    'detail_group_name'
                ],
                'string',
                'max' => 1000
            ],
            [
                [
                    'receipt_allowed',
                    'special_right',
                    'is_combined_competitive_group',
                ],
                'boolean'
            ],
            [
                ['competitive_group_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredCompetitiveGroupReferenceType::class,
                'targetAttribute' => ['competitive_group_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['parent_combined_competitive_group_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredCompetitiveGroupReferenceType::class,
                'targetAttribute' => ['parent_combined_competitive_group_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['subdivision_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredSubdivisionReferenceType::class,
                'targetAttribute' => ['subdivision_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['graduating_department_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredSubdivisionReferenceType::class,
                'targetAttribute' => ['graduating_department_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['branch_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredSubdivisionReferenceType::class,
                'targetAttribute' => ['branch_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['direction_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredDirectionReferenceType::class,
                'targetAttribute' => ['direction_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['profile_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredProfileReferenceType::class,
                'targetAttribute' => ['profile_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['education_level_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredEducationLevelReferenceType::class,
                'targetAttribute' => ['education_level_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['education_form_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredEducationFormReferenceType::class,
                'targetAttribute' => ['education_form_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['education_program_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => EducationType::class,
                'targetAttribute' => ['education_program_ref_id' => 'id']
            ],
            [
                ['education_source_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredEducationSourceReferenceType::class,
                'targetAttribute' => ['education_source_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['budget_level_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredBudgetLevelReferenceType::class,
                'targetAttribute' => ['budget_level_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['detail_group_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredDetailGroupReferenceType::class,
                'targetAttribute' => ['detail_group_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['campaign_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['campaign_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['curriculum_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredCurriculumReferenceType::class,
                'targetAttribute' => ['curriculum_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['ugs_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredUGSReferenceType::class,
                'targetAttribute' => ['ugs_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'group_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "group_code": `Код конкурсной группы`'),
            'group_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "group_name": `Конкурсная группа`'),
            'profil_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "profil_code": `Код специализации`'),
            'profil_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "profil_name": `Специализация`'),
            'eduform_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "eduform_code": `Код формы обучения`'),
            'eduform_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "eduform_name": `Форма обучения`'),
            'faculty_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "faculty_code": `Код факультета`'),
            'faculty_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "faculty_name": `Название факультета`'),
            'finance_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "finance_code": `Код источника финансирования`'),
            'finance_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "finance_name": `Источник финансирования`'),
            'edulevel_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "edulevel_code": `Код уровня образования`'),
            'edulevel_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "edulevel_name": `Уровень образования`'),
            'special_right' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "special_right": `Преимущественное право`'),
            'eduprogram_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "eduprogram_code": `Код образовательной программы`'),
            'eduprogram_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "eduprogram_name": `Образовательная программа`'),
            'speciality_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "speciality_code": `Код направления`'),
            'speciality_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "speciality_name": `Наименование направления`'),
            'budget_level_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "budget_level_name": `Уровень бюджета`'),
            'detail_group_name' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "detail_group_name": `Особенность приема`'),
            'speciality_human_code' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "speciality_human_code": `Код специальности`'),
            'graduatingDepartmentName' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "graduatingDepartmentName": `Кафедра`'),
            'curriculum_ref_id' => Yii::t('abiturient/bachelor/application/dictionary-speciality', 'Подпись для поля "curriculum_ref_id": `Учебный план`'),
        ];
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

    public function isSpecialQuota(): bool
    {
        $detail_group = $this->detailGroupRef;
        if (!$detail_group) {
            return false;
        }
        return $detail_group->reference_uid === Yii::$app->configurationManager->getCode('special_quota_detail_group_guid');
    }

    public function haveOnlyCommonBasis()
    {
        $eduSourceReferenceUid = $this->educationSourceRef->reference_uid ?? null;
        return $eduSourceReferenceUid === BachelorSpeciality::getTargetReceptionBasis() || $eduSourceReferenceUid === BachelorSpeciality::getCommercialBasis();
    }

    public function getCampaign()
    {
        return $this->hasOne(AdmissionCampaign::class, ['code' => 'campaign_code'])
            ->andWhere([AdmissionCampaign::tableName() . '.archive' => false]);
    }

    public function getCategories(bool $allowBenefitCategories = true)
    {
        $categories = AdmissionCategory::find()->notMarkedToDelete()->active();

        $queryWithOutSpecificLaw = ['!=', 'ref_key', Yii::$app->configurationManager->getCode('category_specific_law')];
        if ($allowBenefitCategories) {
            if ($this->haveOnlyCommonBasis()) {
                $categories->andWhere([
                    'ref_key' => Yii::$app->configurationManager->getCode('category_all')
                ]);
            } elseif ((bool)$this->special_right) {
                $categories->andWhere([
                    'ref_key' => Yii::$app->configurationManager->getCode('category_specific_law')
                ]);
            } else {
                $categories->andWhere($queryWithOutSpecificLaw);
            }
        } else {
            $categories->andWhere($queryWithOutSpecificLaw);
        }

        return $categories->all();
    }

    public function getCampaignInfosQuery()
    {
        return CampaignInfo::find()
            ->active()
            ->joinWith('educationLevelRef education_level_ref', false)
            ->joinWith('educationSourceRef education_source_ref', false)
            ->joinWith('educationFormRef education_form_ref', false)
            ->joinWith('detailGroupRef detail_group_ref', false)
            ->joinWith('admissionCategory admission_category', false)
            ->joinWith(['campaign' => function ($q) {
                $q->joinWith('referenceType campaign_ref', false);
            }])
            ->andWhere([
                'campaign_ref.reference_uid' => $this->campaignRef->reference_uid,
                'education_source_ref.reference_uid' => $this->educationSourceRef->reference_uid,
                'education_form_ref.reference_uid' => $this->educationFormRef->reference_uid,
                'detail_group_ref.reference_uid' => ($this->detailGroupRef->reference_uid ?? null),
                'education_level_ref.reference_uid' => ($this->educationLevelRef->reference_uid ?? null),
            ]);
    }

    






    public function getAvailableCategories(bool $allowBenefitCategories = true)
    {
        $categories = $this->getCategories($allowBenefitCategories);
        foreach ($categories as $key => $category) {
            $infos = $this->getCampaignInfosQuery()
                ->andWhere([
                    'admission_category.ref_key' => $category->ref_key,
                ]);
            if ($infos->exists()) {
                $date = date('Y-m-d H:i:s');
                $info_exists = $infos
                    ->andWhere(['>=', IndependentQueryManager::strToDateTime('campaign_info.date_final'), $date])
                    ->andWhere(['<=', IndependentQueryManager::strToDateTime('campaign_info.date_start'), $date])
                    ->limit(1)
                    ->exists();
                if (!$info_exists) {
                    unset($categories[$key]);
                }
            }
        }

        return $categories;
    }

    public function checkCategory(?string $categoryUid): bool
    {
        return $this->getCampaignInfosQuery()
            ->andWhere([
                'admission_category.ref_key' => $categoryUid
            ])
            ->exists();
    }

    public function getCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    public function getCompetitiveGroupRef()
    {
        return $this->hasOne(StoredCompetitiveGroupReferenceType::class, ['id' => 'competitive_group_ref_id']);
    }

    public function getParentCombinedCompetitiveGroupRef()
    {
        return $this->hasOne(StoredCompetitiveGroupReferenceType::class, ['id' => 'parent_combined_competitive_group_ref_id']);
    }

    public function getSubdivisionRef()
    {
        return $this->hasOne(StoredSubdivisionReferenceType::class, ['id' => 'subdivision_ref_id']);
    }

    public function getDirectionRef()
    {
        return $this->hasOne(StoredDirectionReferenceType::class, ['id' => 'direction_ref_id']);
    }

    public function getProfileRef()
    {
        return $this->hasOne(StoredProfileReferenceType::class, ['id' => 'profile_ref_id']);
    }

    public function getEducationLevelRef()
    {
        return $this->hasOne(StoredEducationLevelReferenceType::class, ['id' => 'education_level_ref_id']);
    }

    public function getEducationFormRef()
    {
        return $this->hasOne(StoredEducationFormReferenceType::class, ['id' => 'education_form_ref_id']);
    }

    public function getEducationProgramRef()
    {
        return $this->hasOne(EducationType::class, ['id' => 'education_program_ref_id']);
    }

    public function getEducationSourceRef()
    {
        return $this->hasOne(StoredEducationSourceReferenceType::class, ['id' => 'education_source_ref_id']);
    }

    public function getBudgetLevelRef()
    {
        return $this->hasOne(StoredBudgetLevelReferenceType::class, ['id' => 'budget_level_ref_id']);
    }

    public function getDetailGroupRef()
    {
        return $this->hasOne(StoredDetailGroupReferenceType::class, ['id' => 'detail_group_ref_id']);
    }

    public function getCurriculumRef()
    {
        return $this->hasOne(StoredCurriculumReferenceType::class, ['id' => 'curriculum_ref_id']);
    }

    public function getUgsRef()
    {
        return $this->hasOne(StoredUGSReferenceType::class, ['id' => 'ugs_ref_id']);
    }

    public function getGraduatingDepartmentRef()
    {
        return $this->hasOne(StoredSubdivisionReferenceType::class, ['id' => 'graduating_department_ref_id']);
    }

    public function getBranchRef()
    {
        return $this->hasOne(StoredSubdivisionReferenceType::class, ['id' => 'branch_ref_id']);
    }

    public function getGraduatingDepartmentName()
    {
        return ArrayHelper::getValue($this->graduatingDepartmentRef, 'reference_name');
    }

    









    public static function getByOneSData(StoredAdmissionCampaignReferenceType $campaign_ref, $application_from_1c): Speciality
    {
        $row_speciality = ToAssocCaster::getAssoc($application_from_1c);
        return Speciality::GetFromRaw(
            $campaign_ref,
            ReferenceTypeManager::GetOrCreateReference(
                StoredCompetitiveGroupReferenceType::class,
                $row_speciality['CompetitiveGroupRef'] ?? null
            ),
            ReferenceTypeManager::GetOrCreateReference(
                StoredCurriculumReferenceType::class,
                $row_speciality['CurriculumRef'] ?? null
            ),
            ReferenceTypeManager::GetOrCreateReference(
                StoredProfileReferenceType::class,
                $row_speciality['ProfileRef'] ?? null
            ),
            ReferenceTypeManager::GetOrCreateReference(
                StoredEducationSourceReferenceType::class,
                $row_speciality['EducationSourceRef'] ?? null
            ),
            ReferenceTypeManager::GetOrCreateReference(
                StoredBudgetLevelReferenceType::class,
                $row_speciality['LevelBudgetRef'] ?? null
            )
        );
    }

    public function getParentCombinedCompetitiveGroupRefSpeciality()
    {
        return $this->hasOne(Speciality::class, [
            'campaign_ref_id' => 'campaign_ref_id',
            'competitive_group_ref_id' => 'parent_combined_competitive_group_ref_id'
        ])
            ->active()
            ->andWhere([Speciality::tableName() . '.is_combined_competitive_group' => true]);
    }

    public function getChildrenCombinedCompetitiveGroupRefSpecialities()
    {
        return $this->hasMany(Speciality::class, [
            'campaign_ref_id' => 'campaign_ref_id',
            'parent_combined_competitive_group_ref_id' => 'competitive_group_ref_id'
        ])
            ->active();
    }

    public static function GetFromRaw(
        ?StoredAdmissionCampaignReferenceType $admissionCampaignReferenceType,
        ?StoredCompetitiveGroupReferenceType  $competitiveGroupReferenceType,
        ?StoredCurriculumReferenceType        $curriculumReferenceType,
        ?StoredProfileReferenceType           $profileReferenceType,
        ?StoredEducationSourceReferenceType   $educationSourceReferenceType,
        ?StoredBudgetLevelReferenceType       $budgetLevelReferenceType
    ): Speciality
    {
        
        $local_dict_spec = Speciality::find()
            ->joinWith([
                'campaignRef campaign_ref',
                'competitiveGroupRef competitive_group_ref',
                'profileRef profile_ref',
                'educationSourceRef education_source_ref',
                'budgetLevelRef budget_level_ref',
                'curriculumRef curriculum_ref',
            ])
            ->andWhere([Speciality::tableName() . '.archive' => false])
            ->andFilterWhere(['campaign_ref.reference_uid' => ArrayHelper::getValue(
                $admissionCampaignReferenceType,
                'reference_uid'
            )])
            ->andFilterWhere(['competitive_group_ref.reference_uid' => ArrayHelper::getValue(
                $competitiveGroupReferenceType,
                'reference_uid'
            )])
            ->andFilterWhere(['curriculum_ref.reference_uid' => ArrayHelper::getValue(
                $curriculumReferenceType,
                'reference_uid'
            )])
            ->andFilterWhere(['profile_ref.reference_uid' => ArrayHelper::getValue(
                $profileReferenceType,
                'reference_uid'
            )])
            ->andFilterWhere(['education_source_ref.reference_uid' => ArrayHelper::getValue(
                $educationSourceReferenceType,
                'reference_uid'
            )])
            ->andFilterWhere(['budget_level_ref.reference_uid' => ArrayHelper::getValue(
                $budgetLevelReferenceType,
                'reference_uid'
            )])
            ->one();
        if (empty($local_dict_spec)) {
            throw new UserException("Не удалось найти направление подготовки");
        }
        return $local_dict_spec;
    }

    public function getAdditionalReceiptDateControls()
    {
        return $this->hasMany(AdditionalReceiptDateControl::class, [
            'campaign_ref_id' => 'campaign_ref_id',
            'competitive_group_ref_id' => 'competitive_group_ref_id',
        ]);
    }

    public function getFormattedAdditionalReceiptDates()
    {
        return array_reduce(
            $this->additionalReceiptDateControls,
            function (string $carry, AdditionalReceiptDateControl $additionalReceiptDateControl) {
                if ($carry) {
                    $carry = "{$carry}; ";
                }
                return trim("{$carry}{$additionalReceiptDateControl->formatted_date_start} - {$additionalReceiptDateControl->formatted_date_end}");
            },
            ''
        );
    }

    public function isActiveByAdditionalReceiptDateControl(): bool
    {
        if (!$this->additionalReceiptDateControls) {
            return true;
        }
        $current_time = time();
        foreach ($this->additionalReceiptDateControls as $additionalReceiptDateControl) {
            if ($additionalReceiptDateControl->date_start_timestamp <= $current_time && $current_time < $additionalReceiptDateControl->date_end_timestamp) {
                return true;
            }
        }
        return false;
    }

    public function isTargetReceipt(): bool
    {
        $eduSourceReferenceUid = $this->educationSourceRef->reference_uid ?? null;
        return $eduSourceReferenceUid == BachelorSpeciality::getTargetReceptionBasis();
    }

    public function getFullName(ApplicationType $applicationType = null): string
    {
        $display_speciality_name = ArrayHelper::getValue($applicationType, 'display_speciality_name', true);
        $display_group_name = ArrayHelper::getValue($applicationType, 'display_group_name', true);
        $display_code = ArrayHelper::getValue($applicationType, 'display_code', true);

        $result = '';
        if ($display_code) {
            $result = $this->speciality_human_code . ' ';
        }
        $result .= ($display_speciality_name ? ($this->directionRef->reference_name ?? '') : '')
            . ' '
            . ($display_group_name ? ($this->competitiveGroupRef->reference_name ?? '') : '')
            . ' '
            . ($this->educationLevelRef->reference_name ?? '');
        return trim((string)$result);
    }

    public function getDictionaryCompetitiveGroupEntranceTests()
    {
        return $this->hasMany(DictionaryCompetitiveGroupEntranceTest::class, [
            'campaign_ref_id' => 'campaign_ref_id',
            'curriculum_ref_id' => 'curriculum_ref_id',
            'competitive_group_ref_id' => 'competitive_group_ref_id',
        ]);
    }
}
