<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\CampaignInfo;

class StoredEducationFormReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%education_form_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ФормаОбучения';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler(
            $this,
            Speciality::class,
            'education_form_ref_id',
            'eduform_code'
        ))
            ->fill();

        (new BaseFillHandler(
            $this,
            CampaignInfo::class,
            'education_form_ref_id',
            'eduform_code'
        ))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'education_form_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            CampaignInfo::class,
            'education_form_ref_id'
        ))
            ->restore();
    }
}
