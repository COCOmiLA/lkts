<?php

use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\views\bachelor\assets\CentralizedTestingAsset;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap4\Alert;
use yii\web\View;










$appLanguage = Yii::$app->language;

CentralizedTestingAsset::register($this);

$centralTestingRowId = "collapse-centralized-testing_{$index}";

?>

<?php if (!$centralizedTesting->isNewRecord) : ?>
    <div class="row">
        <div class="col-12">
            <?= $this->render(
                '@abiturientViews/_document_check_status_render',
                ['model' => $centralizedTesting]
            ) ?>
        </div>
    </div>
<?php endif ?>

<div class="row">
    <div class="col-12 col-md-3">
        <?= $form->field($centralizedTesting, "[{$index}]passed_subject_ref_id")
            ->widget(
                Select2::class,
                [
                    'language' => $appLanguage,
                    'data' => $centralizedTesting->passedSubjectList,
                    'options' => [
                        'disabled' => $disable,
                        'placeholder' => Yii::t(
                            'abiturient/bachelor/centralized_testing/centralized_testing-result',
                            'Подпись пустого значения для поля "passed_subject_ref_id" таблицы результатов ЦТ; на стр. ВИ: `Выберите ...`'
                        ),
                        'data-at_least_one_is_required' => 1,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => "#{$centralTestingRowId}"
                    ],
                ]
            ) ?>
    </div>

    <div class="col-12 col-md-3">
        <?= $form->field($centralizedTesting, "[{$index}]series")
            ->textInput([
                'disabled' => $disable,
                'data-at_least_one_is_required' => 1,
            ]) ?>
    </div>

    <div class="col-12 col-md-2">
        <?= $form->field($centralizedTesting, "[{$index}]number")
            ->textInput([
                'disabled' => $disable,
                'data-at_least_one_is_required' => 1,
            ]) ?>
    </div>

    <div class="col-12 col-md-2">
        <?= $form->field($centralizedTesting, "[{$index}]year")
            ->widget(
                DatePickerMaskedWidget::class,
                [
                    'inline' => false,
                    'language' => $appLanguage,
                    'options' => [
                        'autocomplete' => 'off',
                        'disabled' => $disable,
                        'data-at_least_one_is_required' => 1,
                    ],
                    'clientOptions' => [
                        'format' => 'yyyy',
                        'maxViewMode' => 2,
                        'minViewMode' => 2,
                        'autoclose' => true,
                        'startDate' => '1900',
                        'orientation' => 'top left',
                        'endDate' => (string)date('Y'),
                    ],
                ]
            ) ?>
    </div>

    <div class="col-12 col-md-2">
        <?= $form->field($centralizedTesting, "[{$index}]mark")
            ->textInput([
                'type' => 'number',
                'disabled' =>  $disable,
                'data-at_least_one_is_required' => 1,
            ]) ?>
    </div>
</div>

<?php if (!$disable) : ?>
    <div class="row" id="warning-message-<?= $index ?>-centralized_testing">
        <div class="col-12">
            <?= Alert::widget([
                'closeButton' => false,
                'options' => ['class' => 'alert-warning'],
                'body' => Yii::t(
                    'abiturient/bachelor/centralized_testing/centralized_testing-result',
                    'текст алерта когда пользователь не заполнил все обязательные в таблице результатов ЦТ; на стр. ВИ: `Для сохранения результатов вступительных испытаний заполните недостающие данные или сверните не интересующие вас блоки`'
                ),
            ]) ?>
        </div>
    </div>
<?php endif;