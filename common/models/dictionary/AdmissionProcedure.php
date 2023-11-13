<?php

namespace common\models\dictionary;

use common\components\queries\ArchiveQuery;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\interfaces\IArchiveQueryable;
use common\models\ModelLinkedToReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use yii\behaviors\TimestampBehavior;










class AdmissionProcedure extends ModelLinkedToReferenceType implements IArchiveQueryable
{
    use ScenarioWithoutExistValidationTrait;

    protected static $refKeyColumnName = null;

    protected static $refColumns = [
        'admission_campaign_ref_id' => 'CampaignRef',
        'education_source_ref_id' => 'EducationSourceRef',
        'admission_category_id' => 'AdmissionCategoryRef',
        'privilege_id' => 'BenefitRef',
        'special_mark_id' => 'SpecialMarkRef',
    ];

    protected static $refAdditionalClasses = [
        'admission_campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'education_source_ref_id' => StoredEducationSourceReferenceType::class,
        'admission_category_id' => AdmissionCategory::class,
        'privilege_id' => Privilege::class,
        'special_mark_id' => SpecialMark::class,
    ];

    


    public static function tableName()
    {
        return '{{%dictionary_admission_procedure}}';
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
            [['id_pk', 'category_code', 'special_mark_code', 'individual_value', 'priority_right', 'finance_code', 'privilege_code'], 'safe'],
            [['id_pk', 'category_code', 'special_mark_code', 'privilege_code'], 'string', 'max' => 255],
            [['archive', 'individual_value', 'priority_right'], 'boolean'],
            [['admission_campaign_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredAdmissionCampaignReferenceType::class, 'targetAttribute' => ['admission_campaign_ref_id' => 'id'], 'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]],
            [['admission_category_id'], 'exist', 'skipOnError' => false, 'targetClass' => AdmissionCategory::class, 'targetAttribute' => ['admission_category_id' => 'id'], 'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]],
            [['education_source_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredEducationSourceReferenceType::class, 'targetAttribute' => ['education_source_ref_id' => 'id'], 'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]],
            [['privilege_id'], 'exist', 'skipOnError' => false, 'targetClass' => Privilege::class, 'targetAttribute' => ['privilege_id' => 'id'], 'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]],
            [['special_mark_id'], 'exist', 'skipOnError' => false, 'targetClass' => SpecialMark::class, 'targetAttribute' => ['special_mark_id' => 'id'], 'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]],
        ];
    }

    


    public function getAdmissionCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'admission_campaign_ref_id']);
    }

    


    public function getAdmissionCategory()
    {
        return $this->hasOne(AdmissionCategory::class, ['id' => 'admission_category_id']);
    }

    


    public function getEducationSourceRef()
    {
        return $this->hasOne(StoredEducationSourceReferenceType::class, ['id' => 'education_source_ref_id']);
    }

    


    public function getPrivilege()
    {
        return $this->hasOne(Privilege::class, ['id' => 'privilege_id']);
    }

    


    public function getSpecialMark()
    {
        return $this->hasOne(SpecialMark::class, ['id' => 'special_mark_id']);
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
}
