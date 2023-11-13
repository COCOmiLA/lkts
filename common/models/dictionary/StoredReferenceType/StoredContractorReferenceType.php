<?php

namespace common\models\dictionary\StoredReferenceType;

use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredContractorReferenceType extends StoredReferenceType implements 
    IFillableReferenceDictionary, 
    IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%contractor_reference_type}}';
    }
    
    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.Контрагенты';
    }

    public function fillDictionary()
    {
        
    }

    public function restoreDictionary()
    {
        
    }
}
