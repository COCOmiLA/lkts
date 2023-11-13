<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredAchievementCategoryReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{

    public static function tableName()
    {
        return '{{%achievement_category_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ИндивидуальныеДостижения';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            IndividualAchievementType::class,
            'ach_category_ref_id',
            'code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            IndividualAchievementType::class,
            'ach_category_ref_id'))
            ->restore();
    }
}