<?php

namespace common\components\EntranceTestsRenderer;

use common\modules\abiturient\models\bachelor\EgeResult;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;





class DisciplineRenderer extends BaseRenderer
{
    public static function getLabel(): ?string
    {
        return Yii::t(
            'abiturient/bachelor/ege/competitive-group-entrance-tests',
            'Подпись поля "discipline" таблицы наборов ВИ; на стр. ВИ: `Дисциплина`'
        );
    }

    








    public static function renderStaticValue(array $model, string $combinationOfSpecialtyIdAndSubjectRefId): string
    {
        [
            'isChecked' => $isDisciplineChecked,
            'disciplineExist' => $disciplineExist,
        ] = DisciplineRenderer::preprocessDataValue($model, $combinationOfSpecialtyIdAndSubjectRefId);
        
        

        if (!$disciplineExist) {
            return '';
        }

        $isChosen = $isDisciplineChecked ? '<i class="fa fa-check small_verified_status"></i>  ' : '';
        return $isChosen . $model['discipline'];
    }

    











    public static function renderActiveValue(
        array  $model,
        string $combinationOfSpecialtyIdAndSubjectRefId,
        object $form,
        object $egeResult,
        bool   $disable
    ): string
    {
        $disable = DisciplineRenderer::changeDisableFlagIfSpecialityIsEnlisted($disable, $model);

        [
            'isChecked' => $isDisciplineChecked,
            'disciplineExist' => $disciplineExist,
        ] = DisciplineRenderer::preprocessDataValue($model, $combinationOfSpecialtyIdAndSubjectRefId);
        
        

        if (!$disciplineExist) {
            return '';
        }
        [
            $specialtyId,
            $subjectRefId
        ] = explode('_', $combinationOfSpecialtyIdAndSubjectRefId);

        $key = ArrayHelper::getValue($model, 'priority.value');
        $index = "[{$specialtyId}]";
        if (isset($key)) {
            $index .= "[{$key}]";
        }

        
        
        
        
        
        
        
        $elementType = 'radio';
        if ($model['allowMulti']) {
            $elementType = 'checkbox';
            $index .= "[{$subjectRefId}]";
        }

        $class = 'radio_list_like removableHidden';
        $id = strtolower($egeResult->formName()) . "-{$key}-{$specialtyId}-cget_discipline_id--{$subjectRefId}";
        $hiddenId = strtolower($egeResult->formName()) . "-{$key}-{$specialtyId}-cget_child_discipline_id--{$subjectRefId}";
        
        

        $childSubjectRefId = '';
        if (array_key_exists('parentDiscipline', $model)) {
            $childSubjectRefId = $subjectRefId;
            $subjectRefId = $model['parentDiscipline'];
        }

        $egeResult->index = $id;
        $errorlessTemplate = "{label}\n{input}"; 
        return $form->field($egeResult, "{$index}cget_discipline_id")
                ->{$elementType}([
                    'id' => $id,
                    'disabled' => $disable,
                    'value' => $subjectRefId,
                    'class' => "{$class} mainInput",
                    'data-children_id' => $hiddenId,
                    'checked' => $isDisciplineChecked,
                    'onChange' => 'window.toggleAllRelatedButtons()',
                    'data-buttons' => "{$key}_{$specialtyId}_{$subjectRefId}",
                ])
                ->label($model['discipline']) .
            $form->field(
                $egeResult,
                "{$index}cget_child_discipline_id",
                ['template' => $errorlessTemplate]
            )
                ->{$elementType}([
                    'id' => $hiddenId,
                    'disabled' => $disable,
                    'value' => $childSubjectRefId,
                    'checked' => $isDisciplineChecked,
                    'class' => "{$class} hiddenChildInput",
                    'data-buttons' => "{$key}_{$specialtyId}_{$childSubjectRefId}",
                ])
                ->label(false);
    }

    








    private static function preprocessDataValue(array $model, string $combinationOfSpecialtyIdAndSubjectRefId)
    {
        if (empty($model['discipline'])) {
            return ['disciplineExist' => false, 'isChecked' => false];
        }

        [
            'subjectRef' => $subjectRef,
            'childrenSubjectRef' => $childrenSubjectRef,
            'bachelorSpeciality' => $bachelorSpeciality,
        ] = DisciplineRenderer::generalPreprocessData($model, $combinationOfSpecialtyIdAndSubjectRefId);

        $isDisciplineChecked = false;

        $rowspan = ArrayHelper::getValue($model, 'priority.rowspan');
        if (isset($rowspan) && $rowspan == 1) {
            $isDisciplineChecked = true;
        }

        if (
            !$isDisciplineChecked &&
            $bachelorSpeciality->isEntrantTestSetConfirmed()
        ) {
            $isDisciplineChecked = $bachelorSpeciality->isSubjectInConfirmedEntranceTestsSet($subjectRef, $childrenSubjectRef);
        }

        return ['disciplineExist' => true, 'isChecked' => $isDisciplineChecked];
    }

    




    public static function renderOptions(array $model): array
    {
        if (empty($model['discipline'])) {
            return ['style' => 'display: none;'];
        }
        return ['class' => 'vertical_centric'];
    }
}
