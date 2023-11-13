<?php

namespace common\models;

use common\models\dictionary\AvailableDocumentTypesFromOneS;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAvailableDocumentTypeFilterReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;
use common\models\interfaces\ArchiveModelInterface;
use common\models\traits\ArchiveTrait;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use Yii;
use yii\helpers\ArrayHelper;













class IndividualAchievementDocumentType extends AvailableDocumentTypesFromOneS implements ArchiveModelInterface
{
    use ScenarioWithoutExistValidationTrait;
    use ArchiveTrait;


    public static function tableName()
    {
        return '{{%individual_achievements_document_types}}';
    }

    


    public function rules()
    {
        return [
            [
                [
                    'scan_required',
                    'from1c',
                    'need_one_of_documents'
                ],
                'boolean'
            ],
            [
                ['archived_at'],
                'integer'
            ],
            [
                [
                    'custom_order',
                    'document_type_ref_id',
                    'admission_campaign_ref_id',
                    'document_set_ref_id'
                ],
                'integer'
            ],
            [
                [
                    'admission_campaign_ref_id',
                    'document_type_ref_id',
                    'document_type'
                ],
                'required'
            ],
            [
                [
                    'document_type',
                    'campaign_code'
                ],
                'string',
                'max' => 100
            ],
            [
                ['document_set_code'],
                'string',
                'max' => 255
            ],
            [['archive'], 'boolean'],
            [
                ['document_type_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => DocumentType::class,
                'targetAttribute' => ['document_type_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['admission_campaign_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['admission_campaign_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['document_set_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredDocumentSetReferenceType::class,
                'targetAttribute' => ['document_set_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'campaign_code' => Yii::t('abiturient/individual-achievements-document-types', 'Подпись для поля "campaign_code" формы "Тип документа ИД": `Приемная кампания`'),
            'document_type' => Yii::t('abiturient/individual-achievements-document-types', 'Подпись для поля "document_type" формы "Тип документа ИД": `Тип документа`'),
            'scan_required' => Yii::t('abiturient/individual-achievements-document-types', 'Подпись для поля "scan_required" формы "Тип документа ИД": `Обязательный`'),
            'documentDescription' => Yii::t('abiturient/individual-achievements-document-types', 'Подпись для поля "documentDescription" формы "Тип документа ИД": `Наименование документа`'),
            'document_type_ref_id' => Yii::t('abiturient/individual-achievements-document-types', 'Подпись для поля "document_type_ref_id" формы "Тип документа ИД": `Тип документа`'),
            'admission_campaign_ref_id' => Yii::t('abiturient/individual-achievements-document-types', 'Подпись для поля "admission_campaign_ref_id" формы "Тип документа ИД": `Приемная кампания`'),
        ];
    }

    public function getRequiredLabel()
    {
        if ($this->scan_required) {
            return Yii::t(
                'abiturient/individual-achievements-document-types',
                'Подпись наличия флага "scan_required" что флаг установлен формы "Тип документа ИД": `да`'
            );
        }
        return Yii::t(
            'abiturient/individual-achievements-document-types',
            'Подпись отсутствия флага "scan_required" что флаг не установлен формы "Тип документа ИД": `нет`'
        );
    }

    public function getCampaign()
    {
        return $this->hasOne(AdmissionCampaign::class, ['ref_id' => 'admission_campaign_ref_id']);
    }

    


    public function getDocumentTypeRef()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_ref_id']);
    }

    




    public function getAdmissionCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'admission_campaign_ref_id']);
    }

    




    public function getAvailableDocumentTypeFilterRef()
    {
        return $this->hasMany(StoredAvailableDocumentTypeFilterReferenceType::class, ['id' => 'available_document_type_filter_ref_id'])
            ->viaTable(
                'achievements_document_filter_junction',
                ['individual_achievement_document_type_id' => 'id']
            );
    }

    public function getDocumentDescription()
    {
        return ArrayHelper::getValue($this->documentTypeRef, 'description', '');
    }

    




    public function getDocumentSetRef()
    {
        return $this->hasOne(StoredDocumentSetReferenceType::class, ['id' => 'document_set_ref_id']);
    }
}
