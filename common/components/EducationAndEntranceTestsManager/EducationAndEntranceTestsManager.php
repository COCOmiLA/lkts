<?php

namespace common\components\EducationAndEntranceTestsManager;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorEntranceTestSet;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EducationData;
use yii\db\ActiveQuery;





class EducationAndEntranceTestsManager
{
    




    public static function hasDifferenceBetweenOldAndNewAttributes(EducationData $education): bool
    {
        if (!$education->getOldAttributes() || !$education->hasRelatedBachelorSpecialities()) {
            return false;
        }

        foreach ($education->attributesToCompare as $attr) {
            if ((string) $education->getAttributes()[$attr] !== (string) $education->getOldAttributes()[$attr]) {
                return true;
            }
        }

        return false;
    }

    





    public static function getRelatedEntrantTestSetsQuery(EducationData $education, BachelorApplication $application): ActiveQuery
    {
        $tnEducationData = EducationData::tableName();
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();

        return BachelorEntranceTestSet::find()
            ->select(["{$tnBachelorEntranceTestSet}.id"])
            ->joinWith('bachelorSpeciality')
            ->joinWith('bachelorSpeciality.education')
            ->andWhere(["{$tnEducationData}.id" => $education->id])
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $application->id]);
    }
}
