<?php

namespace common\modules\abiturient\models\bachelor;

use backend\models\SpecialityGroupingMode;
use common\components\queries\ArchiveQuery;
use common\models\AttachmentType;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\interfaces\IArchiveQueryable;
use common\models\ModelLinkedToReferenceType;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\db\ActiveQuery;






















class AdmissionCampaign extends ModelLinkedToReferenceType implements IArchiveQueryable
{
    protected static $refKeyColumnName = 'ref_id';

    protected static $refClass = StoredAdmissionCampaignReferenceType::class;

    public static function tableName()
    {
        return '{{%admission_campaign}}';
    }

    


    public function rules()
    {
        return [
            [
                'code',
                'string',
                'max' => 100
            ],
            [
                'name',
                'string',
                'max' => 1000
            ],
            [
                'limit_type',
                'string',
                'max' => 255
            ],
            [
                'api_token',
                'string',
                'max' => 255
            ],
            [
                [
                    'reception_allowed',
                    'consents_allowed',
                    'multiply_applications_allowed',
                    'max_speciality_count'
                ],
                'number'
            ],
            [
                'code',
                'required'
            ],
            [
                [
                    'archive',
                    'snils_is_required',
                    'common_education_document',
                    'require_previous_passport',
                    'count_target_specs_separately',
                    'allow_multiply_education_documents',
                    'separate_statement_for_full_payment_budget',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'common_education_document',
                    'allow_multiply_education_documents',
                    'separate_statement_for_full_payment_budget',
                ],
                'default',
                'value' => false
            ],
            [
                ['ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['ref_id' => 'id']
            ]
        ];
    }

    public function getReferenceType()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'ref_id']);
    }

    


    public function attributeLabels()
    {
        return [
            'allow_multiply_education_documents' => 'Разрешить указывать несколько документов об образовании в привязке к одному направлению подготовки',
            'code' => 'Код кампании',
            'common_education_document' => 'Включить общий список документов об образовании на все направления подготовки',
            'consents_allowed' => 'Возможность предоставлять согласие о зачислении',
            'count_target_specs_separately' => 'При подсчёте выбранных направлений учитывать каждое целевое направление отдельно',
            'multiply_applications_allowed' => 'Возможность подавать несколько заявлений в одну конкурсную группу',
            'name' => 'Наименование',
            'reception_allowed' => 'Разрешено',
            'require_previous_passport' => 'Требовать заполнение предыдущего паспорта',
            'separate_statement_for_full_payment_budget' => 'Отдельное заявление для бюджета полной оплаты',
            'snils_is_required' => 'Необходимость заполнения СНИЛС',
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        if ($this->archive) {
            $this->name .= " (архивная)";
        }
    }

    


    public function getInfo()
    {
        return $this->getRawInfo()->andOnCondition([CampaignInfo::tableName() . '.archive' => false]);
    }

    


    public function getRawInfo()
    {
        return $this->hasMany(CampaignInfo::class, ['campaign_id' => 'id']);
    }

    


    public function getAttachmentTypes()
    {
        return $this->hasMany(AttachmentType::class, ['admission_campaign_ref_id' => 'ref_id']);
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

    public function getSpecialities()
    {
        return $this->hasMany(Speciality::class, [
            'campaign_ref_id' => 'ref_id'
        ]);
    }

    public function getCompetitiveGroups()
    {
        return $this->referenceType ? $this->referenceType->getCompetitiveGroups() : null;
    }

    public function getMaxSpecialityType()
    {
        return $this->limit_type;
    }

    public function getSpecialityGroupingModes()
    {
        return $this->hasMany(SpecialityGroupingMode::class, ['id' => 'grouping_mode_id'])
            ->viaTable('{{%admission_campaign_grouping_modes_junction}}', ['campaign_id' => 'id']);
    }

    public function addSpecialityGroupingMode(string $code_name, string $description): SpecialityGroupingMode
    {
        $mode = SpecialityGroupingMode::GetOrCreateBy($code_name, $description);
        if (!$this->getSpecialityGroupingModes()->andWhere([SpecialityGroupingMode::tableName() . '.id' => $mode->id])->exists()) {
            $this->link('specialityGroupingModes', $mode);
        }
        return $mode;
    }

    public function resetComputedSpecialityGroupingPriorities()
    {
        $speciality_ids = ApplicationType::find()
            ->select([BachelorSpeciality::tableName() . '.id'])
            ->leftJoin(BachelorApplication::tableName(), BachelorApplication::tableName() . '.type_id = ' . ApplicationType::tableName() . '.id')
            ->leftJoin(BachelorSpeciality::tableName(), BachelorSpeciality::tableName() . '.application_id = ' . BachelorApplication::tableName() . '.id')
            ->where([
                ApplicationType::tableName() . '.campaign_id' => $this->id,
                BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED
            ]);

        SpecialityPriority::deleteAll([
            'bachelor_speciality_id' => $speciality_ids
        ]);
    }

    public function getRawAgreementConditions(): ActiveQuery
    {
        return $this->hasMany(AgreementCondition::class, ['campaign_id' => 'id']);
    }

    public function getAgreementConditions(): ActiveQuery
    {
        return $this->getRawAgreementConditions()->active();
    }
}
