<?php

namespace common\components\EntranceTestsRenderer;

use Yii;





class DirectionTrainingRenderer extends BaseRenderer
{
    public static function getLabel(): ?string
    {
        return Yii::t(
            'abiturient/bachelor/ege/competitive-group-entrance-tests',
            'Подпись поля "subject" таблицы наборов ВИ; на стр. ВИ: `Направление подготовки`'
        );
    }

    




    public static function renderStaticValue(array $model): string
    {
        if (empty($model['subject'])) {
            return '';
        }
        $verifiedStatus = DirectionTrainingRenderer::getIsEnlistedFlagFromModel($model) ? '<i class="fa fa-check small_verified_status"></i>' : '';
        return $model['subject']['value'] . $verifiedStatus;
    }

    




    public static function renderOptions(array $model): array
    {
        $result = ['class' => 'words-destroyer'];
        if (empty($model['subject'])) {
            $result['style'] = 'display: none;';

            return $result;
        }
        $result['rowspan'] = $model['subject']['rowspan'];

        return $result;
    }
}
