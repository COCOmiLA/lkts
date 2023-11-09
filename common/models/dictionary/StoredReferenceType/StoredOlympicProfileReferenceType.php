<?php


namespace common\models\dictionary\StoredReferenceType;

use common\models\dictionary\Olympiad;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;

class StoredOlympicProfileReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%olympic_profile_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ПрофилиОлимпиад';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Olympiad::class,
            'olympic_profile_ref_id',
            'profile'))
            ->setModelRefTypeComparisonColumn('reference_name')
            ->setArchiveQuery([])
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Olympiad::class,
            'olympic_profile_ref_id'))
            ->setArchiveQuery(null)
            ->setModelRefTypeComparisonColumn('reference_name')
            ->restore();
    }
}