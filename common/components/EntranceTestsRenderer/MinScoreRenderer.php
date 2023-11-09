<?php

namespace common\components\EntranceTestsRenderer;

use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;





class MinScoreRenderer extends BaseRenderer
{
    public static function getLabel(): ?string
    {
        return Yii::t(
            'abiturient/bachelor/ege/competitive-group-entrance-tests',
            'Подпись поля "min_score" таблицы наборов ВИ; на стр. ВИ: `Минимальный балл`'
        );
    }

    











    public static function renderStaticValue(
        array  $model,
        string $combinationOfSpecialtyIdAndSubjectRefId,
        array  $chosenExams,
        array  $chosenDisciplines,
        string $disciplineEgeForm
    ): string
    {
        if (empty($model['exam_form'])) {
            return '';
        }
        $index = '';
        $subjectRefId = explode('_', $combinationOfSpecialtyIdAndSubjectRefId)[1];
        if (in_array($subjectRefId, $chosenDisciplines)) {
            $chosenExamIndex = array_search($subjectRefId, $chosenDisciplines);
            $index = $chosenExams[$chosenExamIndex];
        }

        $availableExams = array_map(
            function ($examFormDate) {
                return $examFormDate['name'];
            },
            $model['exam_form']
        );
        $availableExamsId = array_keys($availableExams);
        if (
            empty($index) && !empty($availableExamsId) ||
            !in_array($index, $availableExamsId)
        ) {
            if (in_array($disciplineEgeForm, $availableExamsId)) {
                $index = $disciplineEgeForm;
            } else {
                $index = array_shift($availableExamsId);
            }
        }

        return ArrayHelper::getValue($model, "exam_form.{$index}.minScore", '');
    }

    






    public static function renderOptions(array $model, string $combinationOfSpecialtyIdAndSubjectRefId): array
    {
        if (empty($model['min_score'])) {
            return ['style' => 'display: none;'];
        }
        return [
            'class' => 'super_centric',
            'id' => "score_{$combinationOfSpecialtyIdAndSubjectRefId}",
        ];
    }
}
