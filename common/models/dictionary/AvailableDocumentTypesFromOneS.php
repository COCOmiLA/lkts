<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;

class AvailableDocumentTypesFromOneS extends \common\models\ModelLinkedToReferenceType
{
    protected static $refKeyColumnName = 'document_type_ref_id';
    protected static $refClass = DocumentType::class;

    protected static $refColumns = [
        'admission_campaign_ref_id' => 'CampaignRef',
        'document_set_ref_id' => 'DocumentSetRef',
        'document_type_ref_id' => 'DocumentTypeRef',
    ];

    protected static $refAdditionalClasses = [
        'admission_campaign_ref_id' => StoredAdmissionCampaignReferenceType::class,
        'document_set_ref_id' => StoredDocumentSetReferenceType::class,
        'document_type_ref_id' => DocumentType::class,
    ];
}