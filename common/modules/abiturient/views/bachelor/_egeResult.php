<?php

use common\components\attachmentWidget\AttachmentWidget;
use common\models\AttachmentType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\services\NextStepService;
use common\modules\abiturient\views\bachelor\assets\DatePassingEntranceTestAsset;
use common\modules\abiturient\views\bachelor\assets\EgeResultAsset;
use kartik\select2\Select2;
use kartik\form\ActiveForm;
use yii\bootstrap4\Accordion;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;













EgeResultAsset::register($this);
DatePassingEntranceTestAsset::register($this);
$appLanguage = Yii::$app->language;

$hasCorrectCitizenship = BachelorResultCentralizedTesting::hasCorrectCitizenship($application);
$enableDatePickerForExam = ArrayHelper::getValue($application, 'type.allow_pick_dates_for_exam', false);
$canChangeDateExamFrom1C = ArrayHelper::getValue($application, 'type.can_change_date_exam_from_1c', false);

EgeResultAsset::register($this);
if (!isset($useCurrentYearAsDefault)) {
    $useCurrentYearAsDefault = true;
}

if (!isset($attachments)) {
    $attachments = [];
}

if (!isset($regulations)) {
    $regulations = [];
}

$formId = '';
$isExternalForm = true;
if (empty($form)) {
    $options = [];
    if ($attachments || $regulations) {
        $options = ['enctype' => 'multipart/form-data'];
    }

    $formId = 'ege-attachment';
    $isExternalForm = false;
    $form = ActiveForm::begin([
        'id' => $formId,
        'method' => 'POST',
        'options' => $options,
        'action' => Url::toRoute(['bachelor/ege-save-result', 'id' => $application->id]),
    ]);
}

?>

<?php foreach ($egeResults as $result) : ?>
    <?php 

    $readOnly = $disable || $result->readonly || $result->hasEnlistedBachelorSpecialities();

    $index = $result->id;
    $panelEgeId = "panel-ege-id-{$index}";
    $result->_application = $application;
    $isExam = $result->isExam();
    if ($useCurrentYearAsDefault && empty($result->egeyear)) {
        $result->egeyear = (string)date('Y');
    } ?>

    <div class="card mb-3" id="<?= $panelEgeId ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-11">
                    <div class="row">
                        <div class="col-12 col-md-4 pr-0">
                            <div class="row">
                                <?php if ($result->hasChildren()) : ?>
                                    <div class="col-12 col-sm-6">
                                        <?= $result->getAttributeLabel('cget_discipline_id') ?>

                                        <br />

                                        <div class="help_block_leveler">
                                            "<?= "{$result->cgetDiscipline->reference_name} ({$result->cgetChildDiscipline->reference_name})" ?>
                                            "
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="col-12 col-sm-6">
                                        <?= $result->getAttributeLabel('cget_discipline_id') ?>

                                        <br />

                                        <div class="help_block_leveler">
                                            "<?= $result->cgetDiscipline->reference_name ?>"
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col-12 col-sm-6">
                                    <?= $result->getAttributeLabel('cget_exam_form_id') ?>

                                    <br />

                                    <div class="help_block_leveler">
                                        "<?= $result->cgetExamForm->reference_name ?>"
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-8 pl-0">
                            <div class="d-flex flex-sm-row flex-column justify-content-between">
                                <?php if ($isExam) : ?>
                                    <div class="px-2 flex-fill">
                                        <?= $form->field($result, "[{$index}]reason_for_exam_id")
                                            ->widget(
                                                Select2::class,
                                                [
                                                    'language' => $appLanguage,
                                                    'data' => $result->reasonForExamList,
                                                    'options' => [
                                                        'disabled' => $readOnly,
                                                        'placeholder' => Yii::t(
                                                            'abiturient/bachelor/ege/ege-result-form',
                                                            'Подпись пустого значения для поля "reason_for_exam_id" таблицы результатов ВИ; на стр. ВИ: `Выберите ...`'
                                                        ),
                                                    ],
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                    ],
                                                ]
                                            );
                                        if ($readOnly) {
                                            echo $form->field($result, "[{$index}]reason_for_exam_id")->hiddenInput()->label(false);
                                        } ?>
                                    </div>

                                    <?php if ($application->type->allow_language_selection) : ?>
                                        <div class="px-2 flex-fill">
                                            <?= $form->field($result, "[{$index}]language_id")
                                                ->widget(
                                                    Select2::class,
                                                    [
                                                        'language' => $appLanguage,
                                                        'data' => $result->getLanguageList($result->language_id),
                                                        'options' => [
                                                            'disabled' => $readOnly,
                                                            'placeholder' => Yii::t(
                                                                'abiturient/bachelor/ege/ege-result-form',
                                                                'Подпись пустого значения для поля "language_id" таблицы результатов ВИ; на стр. ВИ: `Выберите ...`'
                                                            ),
                                                        ],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                    ]
                                                );
                                            if ($readOnly) {
                                                echo $form->field($result, "[{$index}]language_id")->hiddenInput()->label(false);
                                            } ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($application->type->allow_special_requirement_selection) : ?>
                                        <div class="px-2 flex-fill">
                                            <?= $form->field($result, "[{$index}]special_requirement_ref_id")
                                                ->widget(
                                                    Select2::class,
                                                    [
                                                        'language' => $appLanguage,
                                                        'data' => $result->specialRequirementList,
                                                        'options' => [
                                                            'disabled' => $readOnly,
                                                            'placeholder' => Yii::t(
                                                                'abiturient/bachelor/ege/ege-result-form',
                                                                'Подпись пустого значения для поля "special_requirement_ref_id" таблицы результатов ВИ; на стр. ВИ: `Выберите ...`'
                                                            ),
                                                        ],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                    ]
                                                );
                                            if ($readOnly) {
                                                echo $form->field($result, "[{$index}]special_requirement_ref_id")->hiddenInput()->label(false);
                                            } ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="px-2 flex-fill">
                                        <?= $form->field($result, "[{$index}]egeyear")
                                            ->widget(
                                                Select2::class,
                                                [
                                                    'language' => $appLanguage,
                                                    'data' => $result->years,
                                                    'options' => [
                                                        'disabled' => $readOnly,
                                                        'placeholder' => Yii::t(
                                                            'abiturient/bachelor/ege/ege-result-form',
                                                            'Подпись пустого значения для поля "egeyear" таблицы результатов ВИ; на стр. ВИ: `Выберите ...`'
                                                        ),
                                                    ],
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                        'dropdownParent' => "#{$panelEgeId}",
                                                    ],
                                                ]
                                            );
                                        if ($readOnly) {
                                            echo $form->field($result, "[{$index}]egeyear")->hiddenInput()->label(false);
                                        } ?>
                                    </div>

                                    <div class="px-2 flex-fill">
                                        <?= $form->field($result, "[{$index}]discipline_points")
                                            ->textInput([
                                                'type' => 'number',
                                                'disabled' => $readOnly,
                                            ]) ?>
                                        <?php
                                        if ($readOnly) {
                                            echo $form->field($result, "[{$index}]discipline_points")->hiddenInput()->label(false);
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (
                        $isExam &&
                        $hasCorrectCitizenship
                    ) : ?>
                        <div class="row">
                            <div class="col-12">
                                <?php 
                                $centralizedTesting = $result->getOrBuildCentralizedTesting();

                                $centralizedTestingLabelColumns = Html::tag(
                                    'div',
                                    $centralizedTesting->labelForCollapse,
                                    ['class' => 'col-11']
                                );
                                if (!$centralizedTesting->isNew && !$readOnly) {
                                    $url = Url::to(['/bachelor/delete-centralized-testing', 'id' => $centralizedTesting->id, 'app_id' => $application->id]);
                                    $centralizedTestingLabelColumns .= Html::tag(
                                        'div',
                                        Html::a('', $url, ['class' => 'fa fa-times']),
                                        ['class' => 'col-1 d-flex align-items-center justify-content-end']
                                    );
                                }
                                $centralizedTestingLabel = Html::tag(
                                    'div',
                                    $centralizedTestingLabelColumns,
                                    ['class' => 'row']
                                );

                                $options = ['id' => "collapse-centralized-testing_{$index}"];
                                if (!$readOnly) {
                                    $options['data-form_index'] = $index;
                                    $options['data-form_name'] = $centralizedTesting->formName();
                                } ?>

                                <?= Accordion::widget([
                                    'encodeLabels' => false,
                                    'options' => $options,
                                    'itemToggleOptions' => ['class' => 'btn-block p-0'],
                                    'items' => [
                                        [
                                            'label' => $centralizedTestingLabel,
                                            'content' => $this->render(
                                                '_centralizedTesting',
                                                [
                                                    'form' => $form,
                                                    'index' => $index,
                                                    'disable' => $readOnly,
                                                    'centralizedTesting' => $centralizedTesting,
                                                ]
                                            ),
                                        ],
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-1">
                    <?php if ($result->status == EgeResult::STATUS_VERIFIED) : ?>
                        <div style="text-align: center;">
                            <i class="fa fa-check verified_status super_centric"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (
                $isExam &&
                $enableDatePickerForExam &&
                $result->examSchedule
            ) : ?>
                <hr>

                <?= $this->render(
                    '_egeExamDateForm',
                    [
                        'form' => $form,
                        'result' => $result,
                        'disable' => $readOnly,
                        'canChangeDateExamFrom1C' => $canChangeDateExamFrom1C,
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if (
    !$isExternalForm &&
    (!$disable ||
        $application->hasPassedApplicationWithEditableAttachments(AttachmentType::RELATED_ENTITY_EGE))
) : ?>
    <?php if ($attachments || $regulations) : ?>
        <?= AttachmentWidget::widget([
            'formId' => $formId,
            'attachmentConfigArray' => [
                'application' => $application,
                'items' => $attachments,
                'isReadonly' => $disable,
            ],
            'regulationConfigArray' => [
                'items' => $regulations,
                'form' => $form,
                'isReadonly' => $disable,
            ],
        ]) ?>
    <?php endif; ?>

    <div class="form-group">
        <?php
        $next_step_service = new NextStepService($application);
        $message = Yii::t(
            'abiturient/bachelor/ege/competitive-group-entrance-tests',
            'Подпись кнопки сохранения результатов ВИ; на стр. ВИ: `Сохранить`'
        );
        if ($next_step_service->getUseNextStepForwarding()) {
            $message = Yii::t(
                'abiturient/bachelor/ege/competitive-group-entrance-tests',
                'Подпись кнопки сохранения результатов ВИ; на стр. ВИ: `Сохранить и перейти к следующему шагу`'
            );
        }
        echo Html::submitButton(
            $message,
            ['class' => 'btn btn-primary float-right']
        )
        ?>
    </div>
<?php endif; ?>

<?php if (!$isExternalForm) {
    ActiveForm::end();
}
