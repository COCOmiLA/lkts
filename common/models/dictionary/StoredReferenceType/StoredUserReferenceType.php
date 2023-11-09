<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\interfaces\IFillableReferenceDictionary;

class StoredUserReferenceType extends StoredReferenceType implements IFillableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%user_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ФизическиеЛица';
    }

    public function fillDictionary()
    {
        
        return null;
    }

    public static function isArchivable(): bool
    {
        
        return false;
    }
}