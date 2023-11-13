<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\queries\ArchiveQuery;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\interfaces\IArchiveQueryable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;











class AgreementCondition extends ActiveRecord implements IArchiveQueryable
{
    


    public static function tableName()
    {
        return '{{%agreement_condition}}';
    }

    


    public function rules()
    {
        return [
            [['campaign_id', 'education_source_ref_id'], 'required'],
            [['campaign_id', 'education_source_ref_id'], 'integer'],
            [['campaign_id', 'education_source_ref_id'], 'unique', 'targetAttribute' => ['campaign_id', 'education_source_ref_id']],
            [['campaign_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdmissionCampaign::class, 'targetAttribute' => ['campaign_id' => 'id']],
            [['education_source_ref_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoredEducationSourceReferenceType::class, 'targetAttribute' => ['education_source_ref_id' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'campaign_id' => Yii::t('abiturient/bachelor/agreement-condition', 'Подпись для поля "campaign_id"; формы "Условия использования согласий на зачисление": `ПК`'),
            'education_source_ref_id' => Yii::t('abiturient/bachelor/agreement-condition', 'Подпись для поля "campaign_id"; формы "Условия использования согласий на зачисление": `Основание поступления`'),
        ];
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    


    public function getCampaign()
    {
        return $this->hasOne(AdmissionCampaign::class, ['id' => 'campaign_id']);
    }

    


    public function getEducationSourceRef()
    {
        return $this->hasOne(StoredEducationSourceReferenceType::class, ['id' => 'education_source_ref_id']);
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
