<?php

namespace api\modules\moderator\modules\v1\models\EntrantApplication\decorators;


use api\modules\moderator\modules\v1\models\EntrantApplication\EntrantApplication;
use api\modules\moderator\modules\v1\models\EntrantApplication\EntrantSpeciality;
use api\modules\moderator\modules\v1\models\MasterServerHistory;
use common\components\BooleanCaster;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use yii\helpers\ArrayHelper;









class EntrantApplicationModifiedViewDecorated extends EntrantApplication
{
    public function getMasterServerHistory()
    {
        return $this->hasMany(
            MasterServerHistory::class,
            ['application_id' => 'id']
        );
    }

    public function getLastMasterServerHistory()
    {
        return $this->hasOne(
            MasterServerHistory::class,
            ['application_id' => 'id']
        )
            ->orderBy(['created_at' => SORT_DESC]);
    }

    








    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return ArrayHelper::merge(parent::toArray($fields, $expand, $recursive), [
            'AdditionalInformation' => array_map(function (EntrantSpeciality $speciality) {
                return [
                    'Agreed' => BooleanCaster::toInt(!empty($speciality->agreement)),
                    'EducationFormRef' => ReferenceTypeManager::GetReference($speciality->speciality, 'educationFormRef'),
                    'EducationSourceRef' => ReferenceTypeManager::GetReference($speciality->speciality, 'educationSourceRef'),
                    'CompetitiveGroupRef' => ReferenceTypeManager::GetReference($speciality->speciality, 'competitiveGroupRef'),
                ];
            }, $this->specialities),
        ]);
    }
}
