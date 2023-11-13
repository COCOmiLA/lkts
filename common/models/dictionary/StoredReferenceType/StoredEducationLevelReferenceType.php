<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\EducationDataFilter;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\EducationData;

class StoredEducationLevelReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%education_level_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.УровеньПодготовки';
    }

    public function fillDictionary()
    {
        (new BaseFillHandler(
            $this,
            Speciality::class,
            'education_level_ref_id',
            'edulevel_code'
        ))
            ->fill();
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'education_level_ref_id'
        ))
            ->restore();

        (new BaseRestoreHandler(
            $this,
            EducationDataFilter::class,
            'education_level_id'
        ))
            ->setArchiveQuery(null)
            ->restore();

        (new BaseRestoreHandler(
            $this,
            EducationData::class,
            'education_level_id'
        ))
            ->restore();
    }
}
