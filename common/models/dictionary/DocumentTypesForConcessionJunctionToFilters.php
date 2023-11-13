<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredAvailableDocumentTypeFilterReferenceType;

class DocumentTypesForConcessionJunctionToFilters extends \yii\db\ActiveRecord
{
    public const SUBJECT_TYPE_PRIVILEGES = 'Льготы';
    public const SUBJECT_TYPE_SPECIAL_MARKS = 'ОсобыеОтметки';

    public static function tableName()
    {
        return '{{%document_type_for_concession_filter_junction}}';
    }

    public function rules()
    {
        return [
            ['subject_type', 'string'],
            [['available_document_type_for_concession_id', 'available_document_type_filter_ref_id'], 'integer'],
            [['available_document_type_for_concession_id', 'available_document_type_filter_ref_id', 'subject_type'], 'required'],
            [['available_document_type_for_concession_id'], 'exist', 'skipOnError' => true, 'targetClass' => AvailableDocumentTypesForConcession::class, 'targetAttribute' => ['available_document_type_for_concession_id' => 'id']],
            [['available_document_type_filter_ref_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoredAvailableDocumentTypeFilterReferenceType::class, 'targetAttribute' => ['available_document_type_filter_ref_id' => 'id']],
        ];
    }
}
