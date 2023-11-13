<?php

namespace common\models\dictionary\StoredReferenceType;

use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredContractorTypeReferenceType extends StoredReferenceType implements 
    IFillableReferenceDictionary, 
    IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%contractor_type_reference_type}}';
    }
    
    public static function getReferenceClassToFill(): string
    {
        return 'Справочники.ТипыКонтрагентов';
    }
    
    public function fillDictionary()
    {
        
    }

    public function restoreDictionary()
    {
        
    }

}
