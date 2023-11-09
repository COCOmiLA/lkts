<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredDirectionReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%direction_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.Специальности';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Speciality::class,
            'direction_ref_id',
            'speciality_code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Speciality::class,
            'direction_ref_id'))
            ->restore();
    }
}