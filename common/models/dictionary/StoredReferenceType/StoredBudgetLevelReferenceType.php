<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredBudgetLevelReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{

    public static function tableName()
    {
        return '{{%budget_level_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.УровниБюджета';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Speciality::class,
            'budget_level_ref_id',
            'budget_level_code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Speciality::class,
            'budget_level_ref_id'))
            ->restore();
    }
}