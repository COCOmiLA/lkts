<?php

namespace common\modules\student\models;

use SimpleXMLElement;
use SoapVar;
use stdClass;
use yii\helpers\ArrayHelper;

class ReferenceType
{
    
    public $referenceName;

    
    public $referenceId;

    
    public $referenceUID;

    
    public $referenceClassName;

    
    public $referenceDataVersion;

    
    public $referenceParentUID;

    
    public $is_folder;

    
    public $has_deletion_mark;

    
    public $posted;

    
    public $is_predefined;

    
    public $predefined_data_name;


    




    public static function BuildRefFromXML($data): ?ReferenceType
    {
        if ($data == null) {
            return null;
        }
        if ($data instanceof ReferenceType) {
            return $data;
        }
        if (is_array($data) && ArrayHelper::isAssociative($data)) {
            $data = (object)$data;
        }

        if ($data instanceof SoapVar) {
            $data = $data->enc_value;
        }

        $reference_type = new self();
        $reference_type->fillFieldsFrom1C($data);

        return $reference_type;
    }

    


    public function toObject()
    {
        return ((object)[
            'ReferenceName' => $this->referenceName,
            'ReferenceId' => $this->referenceId,
            'ReferenceUID' => $this->referenceUID,
            'ReferenceClassName' => $this->referenceClassName,
            'ReferenceParentUID' => $this->referenceParentUID,
            'ReferenceDataVersion' => $this->referenceDataVersion,
            'IsFolder' => $this->is_folder,
            'DeletionMark' => $this->has_deletion_mark,
            'Posted' => $this->posted,
            'Predefined' => $this->is_predefined,
            'PredefinedDataName' => $this->predefined_data_name,
        ]);
    }

    


    private function fillFieldsFrom1C($object): void
    {
        $this->posted = (bool)($object->Posted ?? false);
        $this->is_folder = (bool)($object->IsFolder ?? false);
        $this->referenceId = (string)($object->ReferenceId ?? '');
        $this->referenceUID = (string)($object->ReferenceUID ?? '00000000-0000-0000-0000-000000000000');
        $this->is_predefined = (bool)($object->Predefined ?? false);
        $this->referenceName = (string)($object->ReferenceName ?? '');
        $this->has_deletion_mark = (bool)($object->DeletionMark ?? false);
        $this->referenceClassName = (string)($object->ReferenceClassName ?? '');
        $this->referenceParentUID = (string)($object->ReferenceParentUID ?? '00000000-0000-0000-0000-000000000000');
        $this->predefined_data_name = (string)($object->PredefinedDataName ?? '');
        $this->referenceDataVersion = (string)($object->ReferenceDataVersion ?? '');
    }
}
