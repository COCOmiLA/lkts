<?php

namespace common\components\EntranceTestsRenderer;

use common\models\dictionary\StoredReferenceType\StoredChildDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use yii\base\UserException;





class BaseRenderer
{
    








    public static function generalPreprocessData(array $model, string $combinationOfSpecialtyIdAndSubjectRefId): array
    {
        [
            $specialtyId,
            $subjectRefId
        ] = explode('_', $combinationOfSpecialtyIdAndSubjectRefId);

        $childrenSubjectRefId = null;
        if (array_key_exists('parentDiscipline', $model)) {
            $childrenSubjectRefId = $subjectRefId;
            $subjectRefId = $model['parentDiscipline'];
        }

        if (empty($subjectRefId)) {
            throw new UserException("Невозможно определить ID ссылки на дисциплину при отображении таблицы ВИ. Обратитесь к администратору. Индекс, который обрабатывается: {$combinationOfSpecialtyIdAndSubjectRefId}");
        }

        $subjectRef = StoredDisciplineReferenceType::findOne((int)$subjectRefId);
        $childrenSubjectRef = null;
        if ($childrenSubjectRefId) {
            $childrenSubjectRef = StoredChildDisciplineReferenceType::findOne((int)$childrenSubjectRefId);
        }

        if (is_null($subjectRef)) {
            throw new UserException("Невозможно определить ссылку на дисциплину при отображении таблицы ВИ. Обратитесь к администратору. Индекс, который обрабатывается: {$combinationOfSpecialtyIdAndSubjectRefId}, ID специальности: {$specialtyId}");
        }

        
        $bachelorSpeciality = $model['bachelorSpecialityModel'];

        return [
            'subjectRef' => $subjectRef,
            'childrenSubjectRef' => $childrenSubjectRef,
            'bachelorSpeciality' => $bachelorSpeciality,
        ];
    }

    







    public static function changeDisableFlagIfSpecialityIsEnlisted(bool $disable, array $model): bool
    {
        return $disable || BaseRenderer::getIsEnlistedFlagFromModel($model);
    }

    






    public static function getIsEnlistedFlagFromModel(array $model): bool
    {
        return isset($model['bachelorSpecialityModel']) ? $model['bachelorSpecialityModel']->is_enlisted : false;
    }
}
