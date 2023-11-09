<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\StoredReferenceType\StoredAchievementCategoryReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredBudgetLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDetailGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDirectionReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentSetReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationSourceReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicClassReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicKindReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubdivisionReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubjectSetReferenceType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\dictionary\StoredReferenceType\StoredVariantOfRetestReferenceType;
use common\models\interfaces\IFillableReferenceDictionary;




class m211209_165139_restore_references extends MigrationWithDefaultOptions
{
    const REFERENCE_CLASSES = [
        StoredAdmissionCampaignReferenceType::class,
        StoredAchievementCategoryReferenceType::class,
        StoredBudgetLevelReferenceType::class,
        StoredDetailGroupReferenceType::class,
        StoredDirectionReferenceType::class,
        StoredProfileReferenceType::class,
        StoredEducationLevelReferenceType::class,
        StoredEducationFormReferenceType::class,
        StoredCompetitiveGroupReferenceType::class,
        StoredSubjectSetReferenceType::class,
        StoredDisciplineReferenceType::class,
        StoredDisciplineFormReferenceType::class,
        StoredEducationSourceReferenceType::class,
        StoredOlympicProfileReferenceType::class,
        StoredOlympicKindReferenceType::class,
        StoredOlympicLevelReferenceType::class,
        StoredOlympicTypeReferenceType::class,
        StoredVariantOfRetestReferenceType::class,
        StoredDocumentSetReferenceType::class,
        StoredUserReferenceType::class,
        StoredSubdivisionReferenceType::class,
        StoredCurriculumReferenceType::class,
        StoredOlympicReferenceType::class,
        StoredOlympicClassReferenceType::class,
    ];
    
    


    public function safeUp()
    {
        foreach (self::REFERENCE_CLASSES as $reference_class) {
            if (!($reference_class instanceof IFillableReferenceDictionary)) {
                continue;
            }
            
            
            try {
                $reference_class->fillDictionary();
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage(), 'FILL_DICTIONARY');
            }
        }
    }

    


    public function safeDown()
    {
        return true;
    }
}
