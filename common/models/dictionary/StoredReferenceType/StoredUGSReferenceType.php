<?php


namespace common\models\dictionary\StoredReferenceType;

use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;





class StoredUGSReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%ugs_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.УкрупненныеГруппыСпециальностей';
    }

    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
    }
}
