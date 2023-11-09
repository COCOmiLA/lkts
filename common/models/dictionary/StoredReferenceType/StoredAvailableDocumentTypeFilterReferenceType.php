<?php

namespace common\models\dictionary\StoredReferenceType;

class StoredAvailableDocumentTypeFilterReferenceType extends StoredReferenceType
{
    


    public static function tableName()
    {
        return '{{%available_document_type_filter_reference_type}}';
    }

    


    public function rules()
    {
        return [
            [['updated_at', 'created_at'], 'integer'],
            [['archive',], 'boolean'],
            [['reference_name', 'reference_class_name'], 'string', 'max' => 1000],
            [['reference_id', 'reference_uid'], 'string', 'max' => 255],
        ];
    }
}
