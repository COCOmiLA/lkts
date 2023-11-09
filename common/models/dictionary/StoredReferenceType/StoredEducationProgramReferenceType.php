<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\FillHandler\BaseFillHandler;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;






class StoredEducationProgramReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%education_program_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Справочник.ВидыОбразований';
    }

    


    public function fillDictionary()
    {
        (new BaseFillHandler(
            $this,
            Speciality::class,
            'education_program_ref_id',
            'eduprogram_code'
        ))
            ->fill();
    }
    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            Speciality::class,
            'education_program_ref_id'
        ))
            ->restore();
    }
}
