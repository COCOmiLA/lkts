<?php

use common\modules\abiturient\models\bachelor\BachelorDatePassingEntranceTest;
use common\modules\abiturient\models\bachelor\EgeResult;
use kartik\form\ActiveForm;
use yii\web\View;











$model = $result->getOrCreateBachelorDatePassingEntranceTest();

if (!$disable) {
    $disable = $model->from_1c && (!$canChangeDateExamFrom1C);
}

$examList = $result->getExamScheduleList($model);

?>

<div class="row">
    <div class="col-12 first_selector">
        <?= $form->field($model, "[$result->id]bachelor_egeresult_id")
            ->hiddenInput(['disabled' => $disable])
            ->label(false) ?>

        <?= $form->field($model, "[$result->id]date_time_of_exams_schedule_id")
            ->dropDownList(
                $examList,
                [
                    'disabled' => $disable,
                    'data-only_read' => $disable ? 'true' : 'false',
                    'prompt' => Yii::t(
                        'abiturient/bachelor/ege/bachelor-date-passing-entrance-test',
                        'Подпись пустого значения для поля "date_time_of_exams_schedule_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                    ),
                    'onchange' => 'window.checkDatePassing($(this))',
                ]
            ) ?>
    </div>
</div>

<?php
$childModel = $model;
$hasChildren = $model->hasChildren
?>

<?php if ($hasChildren) : ?>
    <?php $index = "[$result->id]"; ?>
    <?php while ($hasChildren) : ?>
        <?php
        $predmetGUIDList = $childModel->existGuidDateTimeList;
        
        $childModel = $childModel->getOrCreateChildren(); ?>

        <?php if (empty($childModel->parent_id)) {
            $index .= '[new]';
        } else {
            $index .= "[$childModel->parent_id]";
        } ?>
        <div class="row">
            <div class="col-12">
                <?php
                $childExamList = $childModel->getExamScheduleList($predmetGUIDList);
                $hasChildren = $childModel->hasChildren;
                $relations = json_encode($childModel->relationList);
                echo $form->field($childModel, "{$index}parent_id")
                    ->hiddenInput(['disabled' => $disable])
                    ->label(false);

                echo $form->field($childModel, "{$index}bachelor_egeresult_id")
                    ->hiddenInput(['disabled' => $disable])
                    ->label(false);

                echo $form->field($childModel, "{$index}date_time_of_exams_schedule_id")
                    ->dropDownList(
                        $childExamList,
                        [
                            'disabled' => $disable,
                            'data-only_read' => $disable ? 'true' : 'false',
                            'prompt' => Yii::t(
                                'abiturient/bachelor/ege/bachelor-date-passing-entrance-test',
                                'Подпись пустого значения для поля "date_time_of_exams_schedule_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                            ),
                            'data-relations' => $relations,
                            'onchange' => 'window.checkDatePassing($(this))',
                        ]
                    ); ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif;