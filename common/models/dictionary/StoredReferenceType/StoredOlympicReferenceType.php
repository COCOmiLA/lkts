<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Olympiad;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredOlympicReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%olympic_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.Олимпиады';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Olympiad::class,
            'ref_id',
            'code'))
            ->setArchiveQuery([])
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Olympiad::class,
            'ref_id'))
            ->setArchiveQuery(null)
            ->restore();
    }
}