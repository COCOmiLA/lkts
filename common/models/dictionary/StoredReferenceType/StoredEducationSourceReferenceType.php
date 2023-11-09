<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\AdmissionProcedure;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CampaignInfo;

class StoredEducationSourceReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%education_source_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ОснованияПоступления';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler($this,
            Speciality::class,
            'education_source_ref_id',
            'finance_code'))
            ->fill();

        (new BaseFillHandler($this,
            CampaignInfo::class,
            'education_source_ref_id',
            'finance_code'))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            Speciality::class,
            'education_source_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            CampaignInfo::class,
            'education_source_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            AdmissionProcedure::class,
            'education_source_ref_id'))
            ->restore();
    }
}