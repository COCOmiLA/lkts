<?php

namespace common\components\EntranceTestsRenderer;

use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\modules\abiturient\models\bachelor\EgeResult;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;
use yii\bootstrap4\Html;





class ExamFormRenderer extends BaseRenderer
{
    public static function getLabel(): ?string
    {
        return Yii::t(
            'abiturient/bachelor/ege/competitive-group-entrance-tests',
            'Подпись поля "exam_form" таблицы наборов ВИ; на стр. ВИ: `Форма сдачи`'
        );
    }

    








    public static function renderStaticValue(array $model, string $combinationOfSpecialtyIdAndSubjectRefId): string
    {
        [
            'value' => $result,
            'formExist' => $formExist,
        ] = ExamFormRenderer::preprocessDataValue($model, $combinationOfSpecialtyIdAndSubjectRefId);
        
        

        if (!$formExist) {
            return '';
        }

        if ($result) {
            return implode(
                ', ',
                array_map(
                    function ($form) {
                        
                        return $form->reference_name;
                    },
                    $result
                )
            );
        } else {
            return '-';
        }
    }

    











    public static function renderActiveValue(
        array  $model,
        string $combinationOfSpecialtyIdAndSubjectRefId,
        object $egeResult,
        string $disciplineEgeForm
    ): string {
        [
            'value' => $egeForms,
            'formExist' => $formExist,
        ] = ExamFormRenderer::preprocessDataValue($model, $combinationOfSpecialtyIdAndSubjectRefId);
        
        

        if (!$formExist) {
            return '';
        }

        $editableForm = null;
        $readonlyForms = [];
        foreach ($egeForms as $formTyp => $egeForm) {
            

            
            
            
            
            
            $editableForm = $egeForm;
            
        }
        $availableExams = array_map(
            function ($examFormDate) {
                return $examFormDate['name'];
            },
            $model['exam_form']
        );

        return ExamFormRenderer::renderEditableBtn(
            $editableForm,
            $model,
            $availableExams,
            $readonlyForms,
            $combinationOfSpecialtyIdAndSubjectRefId,
            $egeResult,
            $disciplineEgeForm
        );
    }

    











    private static function renderEditableBtn(
        $editableForm,
        array $model,
        array $availableExams,
        array $readonlyForms,
        string $combinationOfSpecialtyIdAndSubjectRefId,
        object $egeResult,
        string $disciplineEgeForm
    ) {
        if (!$availableExams) {
            return '';
        }
        if ($editableForm) {
            $egeResult->cget_exam_form_id = $editableForm->id;
        } else {
            $egeResult->cget_exam_form_id = null;
        }

        [
            $specialtyId,
            $subjectRefId
        ] = explode('_', $combinationOfSpecialtyIdAndSubjectRefId);

        $key = ArrayHelper::getValue($model, 'priority.value');

        $availableExamsId = array_keys($availableExams);
        if (
            empty($egeResult->cget_exam_form_id) && !empty($availableExamsId) ||
            !in_array($egeResult->cget_exam_form_id, $availableExamsId)
        ) {
            if (in_array($disciplineEgeForm, $availableExamsId)) {
                $egeResult->cget_exam_form_id = $disciplineEgeForm;
            } else {
                $first = array_shift($availableExamsId);
                $egeResult->cget_exam_form_id = $first;
            }
        }

        $index = "[{$specialtyId}]";
        if (isset($key)) {
            $index .= "[{$key}]";
        }
        $index .= "[{$subjectRefId}][cget_exam_form_id]";
        $id = "{$key}_{$specialtyId}_{$subjectRefId}";

        ksort($availableExams);

        return ExamFormRenderer::renderRadioBtnGroup(
            $model,
            $availableExams,
            $readonlyForms,
            $combinationOfSpecialtyIdAndSubjectRefId,
            $id,
            $index,
            $egeResult
        );
    }

    










    private static function renderRadioBtnGroup(
        array $model,
        array $availableExams,
        array $readonlyForms,
        string $combinationOfSpecialtyIdAndSubjectRefId,
        string $id,
        string $index,
        object $egeResult
    ) {
        $inputs = '';
        foreach ($availableExams as $examId => $exam) {
            $checked = $examId == $egeResult->cget_exam_form_id;
            $disabled = in_array($examId, $readonlyForms);
            $inputs .= Html::tag(
                'label',
                Html::tag(
                    'input',
                    null,
                    [
                        
                        
                        'type' => 'radio', 
                        'value' => $examId,
                        'checked' => $checked,
                        'autocomplete' => 'off',
                        'data-index' => $examId,
                        'disabled' => $disabled,
                        'id' => "{$id}_{$examId}",
                        'name' => "{$egeResult->formName()}{$index}",
                        
                        
                    ]
                ) . $exam,
                [
                    'class' => 'btn btn-outline-secondary uncheckable-radio'
                        . ($checked  ? ' active' : '')
                    
                    
                    
                    
                    
                ]
            );
        }
        return Html::tag(
            'dev',
            $inputs,
            [
                'data-toggle' => 'buttons',
                'onChange' => 'window.changeMinScore($(this))',
                'id' => $id,
                'class' => 'btn-group btn-group-toggle exam_form_buttons',
                'data-score_selector' => "score_{$combinationOfSpecialtyIdAndSubjectRefId}",
                'data-scores' => json_encode(array_map(
                    function ($examFormDate) {
                        return $examFormDate['minScore'];
                    },
                    $model['exam_form']
                )),
            ]
        );
    }

    








    private static function preprocessDataValue(array $model, string $combinationOfSpecialtyIdAndSubjectRefId)
    {
        if (empty($model['exam_form'])) {
            return ['formExist' => false, 'value' => []];
        }

        [
            'subjectRef' => $subjectRef,
            'childrenSubjectRef' => $childrenSubjectRef,
            'bachelorSpeciality' => $bachelorSpeciality,
        ] = ExamFormRenderer::generalPreprocessData($model, $combinationOfSpecialtyIdAndSubjectRefId);

        if (
            $bachelorSpeciality->isEntrantTestSetConfirmed() &&
            $bachelorSpeciality->isSubjectInConfirmedEntranceTestsSet($subjectRef, $childrenSubjectRef)
        ) {
            return ['formExist' => true, 'value' => $bachelorSpeciality->getEntrantTestFormByDiscipline($subjectRef, $childrenSubjectRef)];
        } else {
            return ['formExist' => true, 'value' => []];
        }
    }

    




    public static function renderOptions(array $model): array
    {
        if (empty($model['exam_form'])) {
            return ['style' => 'display: none;'];
        }
        return ['class' => 'vertical_centric'];
    }
}
