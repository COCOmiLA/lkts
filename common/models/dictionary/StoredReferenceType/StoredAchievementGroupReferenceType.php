<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;





class StoredAchievementGroupReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%achievement_group_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ГруппыИндивидуальныхДостижений';
    }

    public function fillDictionary()
    {
    }


    public function restoreDictionary()
    {

    }
}