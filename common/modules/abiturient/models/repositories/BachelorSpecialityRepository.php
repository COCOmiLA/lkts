<?php

namespace common\modules\abiturient\models\repositories;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use yii\db\ActiveQuery;

class BachelorSpecialityRepository
{
    


    public static function GetSpecialitiesByAgreementConditons(BachelorSpeciality $bachelor_spec, array $edu_source_ref_uids = [])
    {
        return $bachelor_spec->application->getSpecialities()
            ->joinWith('speciality.educationSourceRef edu_source_ref', false)
            ->andWhere(['edu_source_ref.reference_uid' => $edu_source_ref_uids])
            ->andWhere(['!=', BachelorSpeciality::tableName() . '.id', $bachelor_spec->id]);
    }
}