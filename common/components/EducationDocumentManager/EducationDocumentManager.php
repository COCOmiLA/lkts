<?php

namespace common\components\EducationDocumentManager;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use Yii;
use yii\web\NotFoundHttpException;

class EducationDocumentManager
{
    public static function DeleteEducationDocument(BachelorApplication $application, int $edu_id): bool
    {
        $result = false;

        $tnEducationData = EducationData::tableName();
        $edu = $application
            ->getEducations()
            ->notInEnlistedApp()
            ->andWhere(["{$tnEducationData}.id" => $edu_id])
            ->one();

        if (empty($edu)) {
            throw new NotFoundHttpException('Не найдена информация об образовании');
        }
        if ($edu->read_only) {
            throw new NotFoundHttpException('Невозможно удалить документ помеченный как «Только для чтения»');
        }
        try {
            $result = $edu->archive();
        } catch (\Throwable $e) {
            Yii::error("Не удалось удалить информацию об образовании: {$e->getMessage()}");
        }
        return $result;
    }
}
