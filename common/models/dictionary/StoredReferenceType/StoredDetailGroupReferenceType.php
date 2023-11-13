<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CampaignInfo;

class StoredDetailGroupReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{

    public static function tableName()
    {
        return '{{%detail_group_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ОсобенностиПриема';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Speciality::class,
            'detail_group_ref_id',
            'detail_group_code'))
            ->fill();

        (new BaseFillHandler($this,
            CampaignInfo::class,
            'detail_group_ref_id',
            'detail_group_code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Speciality::class,
            'detail_group_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            CampaignInfo::class,
            'detail_group_ref_id'))
            ->restore();
    }
}