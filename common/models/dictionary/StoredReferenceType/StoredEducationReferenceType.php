<?php


namespace common\models\dictionary\StoredReferenceType;


class StoredEducationReferenceType extends StoredReferenceType
{
    public static function tableName()
    {
        return '{{%education_reference_type}}';
    }

    public static function isArchivable(): bool
    {
        
        return false;
    }
}