<?php

namespace common\components\EntranceTestsRenderer;

use common\modules\abiturient\models\bachelor\EgeResult;
use Yii;
use yii\base\UserException;
use yii\bootstrap4\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\form\ActiveForm;





class PriorityRenderer extends BaseRenderer
{
    public static function getLabel(): ?string
    {
        return Yii::t(
            'abiturient/bachelor/ege/competitive-group-entrance-tests',
            'Подпись поля "priority" таблицы наборов ВИ; на стр. ВИ: `Приоритет`'
        );
    }

    




    public static function renderStaticValue(array $model): string
    {
        $result = PriorityRenderer::preprocessDataValue($model);
        if (!is_null($result)) {
            return $result;
        }

        return $model['priority']['value'];
    }

    









    public static function renderActiveValue(
        array  $model,
        string $combinationOfSpecialtyIdAndSubjectRefId,
        object $form,
        object $egeResult,
        bool   &$npHasOnlyWithoutEntranceTests
    ): string
    {
        $result = PriorityRenderer::preprocessDataValue($model);
        if (!is_null($result)) {
            return $result;
        }

        $exploded = explode('_', $combinationOfSpecialtyIdAndSubjectRefId);

        $specialtyId = $exploded[0];

        $subjectRefId = $exploded[1];

        if (empty($subjectRefId)) {
            throw new UserException("Невозможно определить ID ссылки на дисциплину при отображении таблицы ВИ. Обратитесь к администратору. Индекс, который обрабатывается: {$combinationOfSpecialtyIdAndSubjectRefId}");
        }

        $key = ArrayHelper::getValue($model, 'priority.value');
        $index = "[{$specialtyId}]";
        if (isset($key)) {
            $index .= "[{$key}]";
        }

        $index .= 'priority';
        $npHasOnlyWithoutEntranceTests = false;

        $egeResult->priority = (int)$model['priority']['value'];
        return Html::tag(
            'div',
            $form->field($egeResult, $index)
                ->hiddenInput()
                ->label($model['priority']['value']),
            ['class' => 'priority-for-ege-result']
        );
    }

    




    private static function preprocessDataValue(array $model)
    {
        if (empty($model['priority']) || ($model['priority']['rowspan'] == 0 && !isset($model['priority']['colspan']))) {
            return '';
        }

        if (isset($model['priority']['colspan']) && !empty($model['priority']['value'])) {
            return Alert::widget([
                'options' => ['class' => 'alert-info'],
                'body' => $model['priority']['value'],
            ]);
        }

        return null;
    }

    




    public static function renderOptions(array $model): array
    {
        if (empty($model['priority']) || ($model['priority']['rowspan'] == 0 && !isset($model['priority']['colspan']))) {
            return ['style' => 'display: none;'];
        }
        if (isset($model['priority']['colspan'])) {
            return [
                'class' => 'super_centric',
                'colspan' => $model['priority']['colspan'],
            ];
        }
        return [
            'class' => 'super_centric',
            'rowspan' => $model['priority']['rowspan'],
        ];
    }
}
