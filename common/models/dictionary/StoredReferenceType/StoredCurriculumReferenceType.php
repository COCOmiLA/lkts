<?php


namespace common\models\dictionary\StoredReferenceType;


use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\OlympiadFilter;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;





class StoredCurriculumReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%curriculum_reference_type}}';
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Документ.УчебныйПлан';
    }

    public function fillDictionary()
    {
        
        
    }


    public function restoreDictionary()
    {
        (new BaseRestoreHandler($this,
            OlympiadFilter::class,
            'curriculum_ref_id'))
            ->setArchiveQuery(null)
            ->restore();

        (new BaseRestoreHandler($this,
            Speciality::class,
            'curriculum_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            DictionaryCompetitiveGroupEntranceTest::class,
            'curriculum_ref_id'))
            ->restore();

        (new BaseRestoreHandler($this,
            IndividualAchievementType::class,
            'ach_curriculum_ref_id'))
            ->restore();
    }
}