<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredCompetitiveGroupReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%competitive_group_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.КонкурсныеГруппы';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Speciality::class,
            'competitive_group_ref_id',
            'group_code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Speciality::class,
            'competitive_group_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            DictionaryCompetitiveGroupEntranceTest::class,
            'competitive_group_ref_id'))
            ->restore();
    }
}