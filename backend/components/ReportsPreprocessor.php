<?php

namespace backend\components;

use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\helpers\ArrayHelper;

class ReportsPreprocessor
{
    public static function getHumanFriendlyIsIn1C(array $data): string
    {
        return ArrayHelper::getValue($data, 'from1C') ? 'есть в 1С' : 'нет в 1С';
    }

    public static function getHumanFriendlyApplicationStatus(array $data): string
    {
        return BachelorApplication::rawTranslateStatus(ArrayHelper::getValue($data, 'abit_status'));
    }

    public static function getHumanFriendlyApplicationDraftStatus(array $data): string
    {
        return BachelorApplication::rawTranslateDraftStatus(ArrayHelper::getValue($data, 'abit_draft_status'));
    }

    public static function preprocessRow(array $data): array
    {
        $data['from1C'] = ReportsPreprocessor::getHumanFriendlyIsIn1C($data);
        $data['abit_status'] = ReportsPreprocessor::getHumanFriendlyApplicationStatus($data);
        $data['abit_draft_status'] = ReportsPreprocessor::getHumanFriendlyApplicationDraftStatus($data);
        return $data;
    }
}
