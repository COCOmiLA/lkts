<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CgetChildSubject;
use common\modules\abiturient\models\bachelor\CgetEntranceTest;

class StoredDisciplineReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%discipline_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.Дисциплины';
    }

    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            CgetChildSubject::class,
            'child_subject_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            CgetEntranceTest::class,
            'subject_ref_id'
        ))
            ->restore();
    }
}
