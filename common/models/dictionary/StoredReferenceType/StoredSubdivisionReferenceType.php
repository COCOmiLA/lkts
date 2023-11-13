<?php


namespace common\models\dictionary\StoredReferenceType;

use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredSubdivisionReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%subdivision_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.СтруктураУниверситета';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler(
            $this,
            Speciality::class,
            'subdivision_ref_id',
            'faculty_code'
        ))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'subdivision_ref_id'
        ))
            ->restore();
        (new BaseRestoreHandler(
            $this,
            DictionaryDateTimeOfExamsSchedule::class,
            'class_room_ref_id'
        ))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn(static::getUidColumnName())
            ->restore();
    }
}
