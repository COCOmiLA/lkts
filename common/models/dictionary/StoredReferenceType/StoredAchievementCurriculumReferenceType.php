<?php


namespace common\models\dictionary\StoredReferenceType;

use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;






class StoredAchievementCurriculumReferenceType extends StoredReferenceType implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{

    public static function tableName()
    {
        return '{{%achievement_curriculum_reference_type}}';
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
            IndividualAchievementType::class,
            'ach_curriculum_ref_id'))
            ->restore();
    }
}
