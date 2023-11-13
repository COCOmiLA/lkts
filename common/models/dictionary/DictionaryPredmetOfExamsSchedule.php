<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\ModelLinkedToReferenceType;
use common\models\traits\ArchiveTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;





















class DictionaryPredmetOfExamsSchedule extends ModelLinkedToReferenceType
{
    use ArchiveTrait;

    


    public static function tableName()
    {
        return '{{%dictionary_predmet_of_exams_schedule}}';
    }

    protected static $refColumns = [
        'campaign_ref_id' => 'CampaignRef',
        'curriculum_ref_id' => 'CurriculumRef',
        'finance_ref_id' => 'FinanceRef',
        'form_ref_id' => 'FormRef',
        'group_ref_id' => 'GroupRef',
        'subject_ref_id' => 'SubjectRef',
    ];

    







    protected static $refAdditionalClasses = [
        'campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'curriculum_ref_id' => StoredCurriculumReferenceType::class,
        'finance_ref_id' => StoredEducationSourceReferenceType::class,
        'form_ref_id' => StoredDisciplineFormReferenceType::class,
        'group_ref_id' => StoredCompetitiveGroupReferenceType::class,
        'subject_ref_id' => StoredDisciplineReferenceType::class,
    ];

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'created_at',
                    'updated_at',
                    'form_ref_id',
                    'group_ref_id',
                    'finance_ref_id',
                    'subject_ref_id',
                    'campaign_ref_id',
                    'curriculum_ref_id',
                ],
                'integer'
            ],
            [
                ['archive'],
                'boolean'
            ],
            [
                ['predmet_guid'],
                'string',
                'max' => 100
            ],
            [
                ['archive'],
                'default',
                'value' => false
            ],
            [
                ['campaign_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['campaign_ref_id' => 'id']
            ],
            [
                ['curriculum_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredCurriculumReferenceType::class,
                'targetAttribute' => ['curriculum_ref_id' => 'id']
            ],
            [
                ['finance_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredEducationSourceReferenceType::class,
                'targetAttribute' => ['finance_ref_id' => 'id']
            ],
            [
                ['form_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDisciplineFormReferenceType::class,
                'targetAttribute' => ['form_ref_id' => 'id']
            ],
            [
                ['group_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredCompetitiveGroupReferenceType::class,
                'targetAttribute' => ['group_ref_id' => 'id']
            ],
            [
                ['subject_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDisciplineReferenceType::class,
                'targetAttribute' => ['subject_ref_id' => 'id']
            ],
        ];
    }

    


    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    




    public function getCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    




    public function getCurriculumRef()
    {
        return $this->hasOne(StoredCurriculumReferenceType::class, ['id' => 'curriculum_ref_id']);
    }

    




    public function getFinanceRef()
    {
        return $this->hasOne(StoredEducationSourceReferenceType::class, ['id' => 'finance_ref_id']);
    }

    




    public function getFormRef()
    {
        return $this->hasOne(StoredDisciplineFormReferenceType::class, ['id' => 'form_ref_id']);
    }

    




    public function getGroupRef()
    {
        return $this->hasOne(StoredCompetitiveGroupReferenceType::class, ['id' => 'group_ref_id']);
    }

    




    public function getSubjectRef()
    {
        return $this->hasOne(StoredDisciplineReferenceType::class, ['id' => 'subject_ref_id']);
    }
}
