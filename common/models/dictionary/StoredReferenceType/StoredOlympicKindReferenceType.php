<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Olympiad;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredOlympicKindReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    protected static $required_fields = [
        'reference_name', 'reference_class_name', 'archive'
    ];

    public static function tableName()
    {
        return '{{%olympic_kind_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Перечисление.ВидыОлимпиад';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Olympiad::class,
            'olympic_kind_ref_id',
            'kind'))
            ->setModelRefTypeComparisonColumn('reference_name')
            ->setArchiveQuery([])
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Olympiad::class,
            'olympic_kind_ref_id'))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn('reference_name')
            ->restore();
    }
}