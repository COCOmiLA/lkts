<?php

namespace common\models\dictionary;


use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredVariantOfRetestReferenceType;
use common\models\ModelLinkedToReferenceType;
use yii\behaviors\TimestampBehavior;















class OlympiadFilter extends ModelLinkedToReferenceType
{

    protected static $refColumns = [
        'campaign_ref_id' => 'CampaignRef',
        'special_mark_id' => 'SpecialMarkRef',
        'olympiad_id' => 'OlympicRef',
        'curriculum_ref_id' => 'CurriculumRef',
        'variant_of_retest_ref_id' => 'VariantOfRetestRef',
    ];

    protected static $refAdditionalClasses = [
        'campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'special_mark_id' => SpecialMark::class,
        'olympiad_id' => Olympiad::class,
        'curriculum_ref_id' => StoredCurriculumReferenceType::class,
        'variant_of_retest_ref_id' => StoredVariantOfRetestReferenceType::class,
    ];

    public function rules()
    {
        return [
            [[
                'campaign_ref_id',
                'special_mark_id',
                'olympiad_id',
                'curriculum_ref_id',
                'variant_of_retest_ref_id',
            ], 'integer'],
            [['campaign_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredAdmissionCampaignReferenceType::class, 'targetAttribute' => ['campaign_ref_id' => 'id']],
            [['special_mark_id'], 'exist', 'skipOnError' => false, 'targetClass' => SpecialMark::class, 'targetAttribute' => ['special_mark_id' => 'id']],
            [['olympiad_id'], 'exist', 'skipOnError' => false, 'targetClass' => Olympiad::class, 'targetAttribute' => ['olympiad_id' => 'id']],
            [['curriculum_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredCurriculumReferenceType::class, 'targetAttribute' => ['curriculum_ref_id' => 'id']],
            [['variant_of_retest_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredVariantOfRetestReferenceType::class, 'targetAttribute' => ['variant_of_retest_ref_id' => 'id']],
        ];
    }

    


    public static function tableName()
    {
        return '{{%dictionary_olympiads_filter}}';
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

    public function getCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    public function getSpecialMarkRef()
    {
        return $this->hasOne(SpecialMark::class, ['id' => 'special_mark_id']);
    }

    public function getOlympiadRef()
    {
        return $this->hasOne(Olympiad::class, ['id' => 'olympiad_id']);
    }

    public function getCurriculumRef()
    {
        return $this->hasOne(StoredCurriculumReferenceType::class, ['id' => 'curriculum_ref_id']);
    }

    public function getVariantOfRetestRef()
    {
        return $this->hasOne(StoredVariantOfRetestReferenceType::class, ['id' => 'variant_of_retest_ref_id']);
    }
}