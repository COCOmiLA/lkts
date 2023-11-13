<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CgetConditionType;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;

class StoredProfileReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%profile_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.Специализации';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler(
            $this,
            Speciality::class,
            'profile_ref_id',
            'profil_code'
        ))->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'profile_ref_id'
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            CgetEntranceTestSet::class,
            'profile_ref_id'
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            CgetConditionType::class,
            'profile_reference_type_id'
        ))->restore();
    }
}
