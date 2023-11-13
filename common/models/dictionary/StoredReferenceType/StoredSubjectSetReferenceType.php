<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;

class StoredSubjectSetReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%subject_set_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.НаборыВступительныхИспытаний';
    }

    


    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            CgetEntranceTestSet::class,
            'entrance_test_set_ref_id'
        ))
            ->restore();
    }
}
