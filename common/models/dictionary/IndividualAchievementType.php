<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\dictionary\StoredReferenceType\StoredAchievementCategoryReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAchievementGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\interfaces\IArchiveQueryable;
use common\models\ModelLinkedToReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;













class IndividualAchievementType extends ModelLinkedToReferenceType implements IArchiveQueryable
{
    use ScenarioWithoutExistValidationTrait;

    protected static $refKeyColumnName = 'ach_category_ref_id';
    protected static $refClass = StoredAchievementCategoryReferenceType::class;
    protected static $refColumns = [
        'campaign_ref_id' => 'CampaignRef',
        'ach_category_ref_id' => 'SubjectRef',
        'ach_curriculum_ref_id' => 'CurriculumRef',
        'achievement_group_ref_id' => 'IndividualAchievementGroupRef',
    ];

    protected static $refAdditionalClasses = [
        'campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'ach_category_ref_id' => StoredAchievementCategoryReferenceType::class,
        'ach_curriculum_ref_id' => StoredCurriculumReferenceType::class,
        'achievement_group_ref_id' => StoredAchievementGroupReferenceType::class,
    ];

    


    public static function tableName()
    {
        return '{{%dictionary_individual_achievement}}';
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
                    'campaign_ref_id',
                    'ach_category_ref_id',
                    'ach_curriculum_ref_id',
                    'achievement_group_ref_id',
                ],
                'integer'
            ],
            [
                [
                    'code',
                    'name',
                    'campaign_code',
                ],
                'required'
            ],
            [
                [
                    'code',
                    'campaign_code'
                ], 'string',
                'max' => 100
            ],
            [
                ['name'], 'string',
                'max' => 1000
            ],
            [['archive', 'points_in_group_are_awarded_once'], 'boolean'],
            [
                ['campaign_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['campaign_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]

            ],
            [
                ['ach_category_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredAchievementCategoryReferenceType::class,
                'targetAttribute' => ['ach_category_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]

            ],
            [
                ['achievement_group_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredAchievementGroupReferenceType::class,
                'targetAttribute' => ['achievement_group_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]

            ],
            [
                ['ach_curriculum_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredCurriculumReferenceType::class,
                'targetAttribute' => ['ach_curriculum_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]

            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'code' => 'код',
            'name' => 'Наименование',
            'campaign_code' => 'код приемной кампании',
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

    public function getAdmissionCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    public function getAchievementCategoryRef()
    {
        return $this->hasOne(StoredAchievementCategoryReferenceType::class, ['id' => 'ach_category_ref_id']);
    }

    public function getAchievementGroupRef()
    {
        return $this->hasOne(StoredAchievementGroupReferenceType::class, ['id' => 'achievement_group_ref_id']);
    }

    public function getAchievementCurriculumRef()
    {
        return $this->hasOne(StoredCurriculumReferenceType::class, ['id' => 'ach_curriculum_ref_id']);
    }

    





    public static function getIaTypesByCampaignAndSpecialitiesQuery(BachelorApplication $application, bool $add_chosen): ArchiveQuery
    {
        $chosen_ids = [];
        if ($add_chosen) {
            $individualAchievements = $application->getIndividualAchievements()
                ->select(['individual_achievement.dictionary_individual_achievement_id']);

            $chosen_ids = IndividualAchievementType::find()
                ->select([IndividualAchievementType::tableName() . '.id', 'achievement_category_ref.reference_uid'])
                ->joinWith('admissionCampaignRef admission_campaign_ref', false)
                ->joinWith('achievementCategoryRef achievement_category_ref')
                ->andWhere(['admission_campaign_ref.reference_uid' => $application->type->rawCampaign->referenceType->reference_uid])
                ->andWhere(['in', IndividualAchievementType::tableName() . '.id', $individualAchievements])
                ->asArray()
                ->all();
        }

        $specialitiesQuery = $application->getSpecialities()
            ->select(['spec_cur_ref.reference_uid'])
            ->joinWith('speciality')
            ->joinWith('speciality.curriculumRef spec_cur_ref')
            ->andWhere(['spec_cur_ref.archive' => false])
            ->groupBy(['spec_cur_ref.reference_uid'])
            ->orderBy(''); 

        $main_ids = IndividualAchievementType::find()
            ->select([IndividualAchievementType::tableName() . '.id', 'achievement_category_ref.reference_uid'])
            ->joinWith('achievementCurriculumRef cur_ref')
            ->joinWith('achievementCategoryRef achievement_category_ref')
            ->joinWith('admissionCampaignRef admission_campaign_ref', false)
            ->andWhere(['admission_campaign_ref.reference_uid' => $application->type->rawCampaign->referenceType->reference_uid])
            ->andWhere([IndividualAchievementType::tableName() . '.archive' => false])
            ->andWhere(['cur_ref.archive' => false])
            ->andWhere(['in', 'cur_ref.reference_uid', $specialitiesQuery])
            ->asArray()
            ->all();


        $ids = [];
        foreach ($chosen_ids as $id) {
            $ids[$id['reference_uid']] = $id['id'];
        }
        foreach ($main_ids as $id) {
            if (!isset($ids[$id['reference_uid']])) {
                $ids[$id['reference_uid']] = $id['id'];
            }
        }
        $ids = array_values($ids);
        return IndividualAchievementType::find()
            ->with(['achievementCategoryRef'])
            ->where([IndividualAchievementType::tableName() . '.id' => array_values(array_unique($ids))]);
    }

    





    public static function getIaTypesByCampaignAndSpecialities(BachelorApplication $application, bool $add_chosen = true)
    {
        $iaNames = [];

        $iaTypes = IndividualAchievementType::getIaTypesByCampaignAndSpecialitiesQuery($application, $add_chosen)
            ->orderBy('name')
            ->all();

        if (empty($iaTypes)) {
            return [];
        }
        foreach ($iaTypes as $iaType) {
            
            $iaNames[$iaType->id] = $iaType->getFullDescription();
        }

        return $iaNames;
    }

    public function getFullDescription()
    {
        $name = $this->name;
        if ($this->achievementGroupRef) {
            $name .= ' (' . $this->achievementGroupRef->reference_name . ')';
        }
        return $name;
    }
}
