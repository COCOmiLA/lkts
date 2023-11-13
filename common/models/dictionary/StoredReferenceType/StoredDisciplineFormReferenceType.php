<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CgetEntranceTest;
use common\modules\abiturient\models\bachelor\EgeResult;

class StoredDisciplineFormReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%discipline_form_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ВидыКонтроля';
    }

    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            CgetEntranceTest::class,
            'entrance_test_result_source_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            EgeResult::class,
            'cget_exam_form_id'
        ))
            ->restore();
    }
}
