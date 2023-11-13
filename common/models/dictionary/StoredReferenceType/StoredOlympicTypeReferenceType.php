<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Olympiad;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredOlympicTypeReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%olympic_type_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ТипыОлимпиад';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Olympiad::class,
            'olympic_type_ref_id',
            'type'))
            ->setArchiveQuery([])
            ->setModelRefTypeComparisonColumn('reference_name')
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Olympiad::class,
            'olympic_type_ref_id'))
            ->setModelRefTypeComparisonColumn('reference_name')
            ->setArchiveQuery(null)
            ->restore();
    }
}