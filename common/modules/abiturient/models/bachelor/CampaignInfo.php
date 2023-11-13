<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\queries\ArchiveQuery;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\StoredReferenceType\StoredDetailGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\interfaces\IArchiveQueryable;
use common\models\ModelLinkedToReferenceType;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;















class CampaignInfo extends ModelLinkedToReferenceType implements IArchiveQueryable
{
    protected static $refKeyColumnName = null;

    
    protected static $refColumns = [
        'education_source_ref_id' => 'EducationSourceRef',
        'education_form_ref_id' => 'EducationFormRef',
        'detail_group_ref_id' => 'DetailGroupRef',
        'education_level_ref_id' => 'EducationLevelRef',
    ];
    protected static $refAdditionalClasses = [
        'education_level_ref_id' => StoredEducationLevelReferenceType::class,
        'education_source_ref_id' => StoredEducationSourceReferenceType::class,
        'education_form_ref_id' => StoredEducationFormReferenceType::class,
        'detail_group_ref_id' => StoredDetailGroupReferenceType::class,
    ];

    public static function tableName()
    {
        return '{{%campaign_info}}';
    }

    


    public function rules()
    {
        return [
            [['finance_code', 'eduform_code', 'detail_group_code', 'category_code'], 'string', 'max' => 100],
            [['campaign_id',], 'integer'],
            [['date_start', 'date_final', 'date_order_start', 'date_order_end'], 'string', 'max' => 100],
            [['campaign_id', 'finance_code', 'eduform_code'], 'required'],
            [['archive'], 'boolean'],
            [['education_source_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredEducationSourceReferenceType::class, 'targetAttribute' => ['education_source_ref_id' => 'id']],
            [['education_form_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredEducationFormReferenceType::class, 'targetAttribute' => ['education_form_ref_id' => 'id']],
            [['admission_category_id'], 'exist', 'skipOnError' => false, 'targetClass' => AdmissionCategory::class, 'targetAttribute' => ['admission_category_id' => 'id']],
            [['detail_group_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredDetailGroupReferenceType::class, 'targetAttribute' => ['detail_group_ref_id' => 'id']],
            [['education_level_ref_id'], 'exist', 'skipOnError' => false, 'targetClass' => StoredEducationLevelReferenceType::class, 'targetAttribute' => ['education_level_ref_id' => 'id'],],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'campaign_id' => 'Приемная кампания кампании',
            'eduform_code' => 'Форма обучения',
            'finance_code' => 'Форма финансирования',
            'detail_group_code' => 'Особенности приема',
            'date_start' => 'Дата начала приема заявлений',
            'date_final' => 'Дата окончания приема заявлений',
            'date_order_start' => 'Дата начала приказа',
            'date_order_end' => 'Дата окончания приказа',
        ];
    }

    public function getCampaign()
    {
        return $this->hasOne(AdmissionCampaign::class, ['id' => 'campaign_id']);
    }

    


    public function getFinanceName()
    {
        return ArrayHelper::getValue($this, 'educationSourceRef.reference_name', '-');
    }

    


    public function getEduformName()
    {
        return ArrayHelper::getValue($this, 'educationFormRef.reference_name', '-');
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

    public function getEducationSourceRef()
    {
        return $this->hasOne(StoredEducationSourceReferenceType::class, [
            'id' => 'education_source_ref_id'
        ]);
    }

    public function getEducationFormRef()
    {
        return $this->hasOne(StoredEducationFormReferenceType::class, [
            'id' => 'education_form_ref_id'
        ]);
    }

    public function getDetailGroupRef()
    {
        return $this->hasOne(StoredDetailGroupReferenceType::class, [
            'id' => 'detail_group_ref_id'
        ]);
    }

    public function getAdmissionCategory()
    {
        return $this->hasOne(AdmissionCategory::class, [
            'id' => 'admission_category_id'
        ]);
    }

    public function getEducationLevelRef()
    {
        return $this->hasOne(StoredEducationLevelReferenceType::class, ['id' => 'education_level_ref_id']);
    }

    public function getPeriodsToSendAgreement()
    {
        return $this->hasMany(PeriodToSendAgreement::class, [
            'campaign_info_id' => 'id'
        ]);
    }

    public function getPeriodsToSendOriginalEducation()
    {
        return $this->hasMany(PeriodToSendOriginalEducation::class, [
            'campaign_info_id' => 'id'
        ]);
    }

    public function updatePeriods($raw_periods)
    {
        if (!is_array($raw_periods)) {
            $raw_periods = array_values(array_filter([$raw_periods])); 
        }
        PeriodToSendAgreement::deleteAll(['campaign_info_id' => $this->id]);
        PeriodToSendOriginalEducation::deleteAll(['campaign_info_id' => $this->id]);
        foreach ($raw_periods as $raw_period) {
            $type = ($raw_period->PeriodTypeRef ?? null)->PredefinedDataName ?? null;
            $new_period = null;
            switch ($type) {
                case 'ПриемСогласийНаЗачисление':
                    $new_period = new PeriodToSendAgreement();
                    $new_period->in_day_of_sending_app_only = boolval($raw_period->AllowAgreementSubmissionOnFirstApplicationDay);
                    $new_period->in_day_of_sending_speciality_only = boolval($raw_period->AllowAgreementSubmissionOnCompetitiveGroupApplicationDay);
                    break;
                case 'ПриемОригиналовДокументовОбОбразовании':
                    $new_period = new PeriodToSendOriginalEducation();
                    break;
            }
            if (!$new_period) {
                \Yii::warning("Не найден тип периода для кампании: {$type}", 'updatePeriods');
                continue;
            }
            $new_period->campaign_info_id = $this->id;
            $new_period->start = date('Y-m-d H:i:s', strtotime((string)$raw_period->DateStart));
            $new_period->end = date('Y-m-d H:i:s', strtotime((string)$raw_period->DateEnd));

            $new_period->save();
        }
    }

    public static function getActiveCampaignsQuery(): ActiveQuery
    {
        $date = date('Y-m-d H:i:s');
        $tnCampaignInfo = CampaignInfo::tableName();
        return CampaignInfo::find()
            ->active()
            ->andWhere(['<', IndependentQueryManager::strToDateTime("{$tnCampaignInfo}.date_start"), $date])
            ->andWhere(['>=', IndependentQueryManager::strToDateTime("{$tnCampaignInfo}.date_final"), $date]);
    }
}
