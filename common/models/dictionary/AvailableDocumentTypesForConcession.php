<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAvailableDocumentTypeFilterReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;
use common\models\ModelLinkedToReferenceType;
use yii\behaviors\TimestampBehavior;









class AvailableDocumentTypesForConcession extends AvailableDocumentTypesFromOneS
{

    public function rules(): array
    {
        return [
            [['need_one_of_documents'], 'boolean'],
            [['updated_at', 'created_at', 'admission_campaign_ref_id', 'document_set_ref_id', 'document_type_ref_id'], 'integer'],
            [['campaign_code', 'document_type', 'document_set_code'], 'string', 'max' => 255],
            [['document_set_code'], 'string', 'max' => 255],
            [['document_type_ref_id'], 'exist', 'skipOnError' => false,
                'targetClass' => DocumentType::class, 'targetAttribute' => ['document_type_ref_id' => 'id']],
            [['admission_campaign_ref_id'], 'exist', 'skipOnError' => false,
                'targetClass' => StoredAdmissionCampaignReferenceType::class, 'targetAttribute' => ['admission_campaign_ref_id' => 'id']],
            [['document_set_ref_id'], 'exist', 'skipOnError' => false,
                'targetClass' => StoredDocumentSetReferenceType::class, 'targetAttribute' => ['document_set_ref_id' => 'id']],
        ];
    }

    


    public static function tableName()
    {
        return '{{%dictionary_available_document_types_for_concession}}';
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

    public function getFilterJunctions()
    {
        return $this->hasMany(DocumentTypesForConcessionJunctionToFilters::class, ['available_document_type_for_concession_id' => 'id']);
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
            ->via('filterJunctions');
    }

    




    public function getDocumentSetRef()
    {
        return $this->hasOne(StoredDocumentSetReferenceType::class, ['id' => 'document_set_ref_id']);
    }
}
