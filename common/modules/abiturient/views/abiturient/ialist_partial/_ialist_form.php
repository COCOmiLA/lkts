<?php

use common\components\ini\iniGet;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\dictionary\IndividualAchievementType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\IndividualAchievement;
use common\widgets\ContractorField\ContractorField;
use common\widgets\TooltipWidget\TooltipWidget;
use frontend\assets\IndividualAchievementsFillAsset;
use kartik\form\ActiveForm;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








IndividualAchievementsFillAsset::register($this);

$appLanguage = Yii::$app->language;

if (!isset($isReadOnly)) {
    $isReadOnly = false;
}
$isReadOnly = (!!$model->read_only) || $isReadOnly;

$isNew = false;
if (!isset($key)) {
    $key = 'new';
    $isNew = true;
}
$id = 'individual-achievement-form-' . $key;
$form = ActiveForm::begin([
    'id' => $id,
    'action' => $action,
    'method' => 'POST',
]);

$app_type = $application->type;

echo Html::hiddenInput(
    'app_code',
    $app_type->id,
    ['id' => "app_type_id-{$key}"]
);

if (!$model->isNewRecord) {
    echo Html::hiddenInput(
        'id_ia',
        $model->id,
        ['id' => "id_ia-{$key}"]
    );
}

echo Html::hiddenInput(
    'fileChanged',
    1,
    [
        'id' => "preview-changed-{$key}",
        'disabled' => true
    ]
);

$wrapper_id = "ia-wrapper-{$key}";
?>
<div class="document-root">
    <div id="<?php echo $wrapper_id ?>">
        <?php if (!$model->isNewRecord) : ?>
            <?= $this->render(
                '@abiturientViews/_document_check_status_render',
                compact(['model'])
            ) ?>
        <?php endif ?>

        <div class="form-group required">
            <div class="row">
                <div class="col-12">
                    <?php $data = [];
                    if (isset($app_type, $app_type->campaign)) {
                        $data = IndividualAchievementType::getIaTypesByCampaignAndSpecialities($application, !$isNew);
                    } ?>

                    <?= $form->field($model, 'dictionary_individual_achievement_id')->widget(Select2::class, [
                        'language' => $appLanguage,
                        'options' => [
                            'disabled' => $isReadOnly,
                            'class' => 'form-control',
                            'id' => "individual-achievement-type-{$key}",
                        ],
                        'data' => $data,
                        'name' => 'IndividualAchievement[dictionary_individual_achievement_id]',
                        'pluginOptions' => [
                            'allowClear' => false,
                            'dropdownParent' => "#{$wrapper_id}",
                            'multiple' => false,
                            'placeholder' => Yii::t(
                                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                                'Подпись пустого значения для поля "dictionary_individual_achievement_id"; в модальном окне ИД на странице ИД: `Выберите ...`'
                            ),
                        ],
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'document_type_id')->widget(DepDrop::class, [
                        'language' => $appLanguage,
                        'options' => [
                            'disabled' => $isReadOnly,
                            'class' => 'form-control',
                            'id' => "app_docum_id-{$key}",
                            'data' => [
                                'document_type_input' => 1,
                                'document_type_id_option_attr' => 'data-document_type_id'
                            ],
                        ],
                        'name' => 'IndividualAchievement[document_type_id]',
                        'type' => DepDrop::TYPE_SELECT2,
                        'select2Options' => ['pluginOptions' => [
                            'dropdownParent' => "#{$wrapper_id}",
                            'allowClear' => false,
                            'multiple' => false,
                            'placeholder' => Yii::t(
                                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                                'Подпись пустого значения для поля "document_type_id"; в модальном окне ИД на странице ИД: `Выберите ...`'
                            ),
                        ]],
                        'pluginOptions' => [
                            'depends' => [
                                "individual-achievement-type-{$key}",
                                "id_ia-{$key}"
                            ],
                            'initialize' => true,
                            'url' => Url::to(['/abiturient/ia-doc-types']),
                            'loadingText' => Yii::t(
                                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                                'Подпись загружаемого значения для поля "document_type_id"; в модальном окне ИД на странице ИД: `Загрузка ...`'
                            ),
                            'placeholder' => Yii::t(
                                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                                'Подпись пустого значения для поля "document_type_id"; в модальном окне ИД на странице ИД: `Выберите ...`'
                            ),
                        ],
                        'pluginEvents' => ['change' => '
                        function (event) {
                            if (event.target) {
                                var option = null;
                                if (event.target.selectedIndex !== null && event.target.selectedIndex !== undefined) {
                                    var option = event.target.options[event.target.selectedIndex];
                                }
                                if (option && option.getAttribute("data-scan_required") === "1") {
                                    $(event.target).parents("form").find(".file-group").addClass("required");
                                } else {
                                    $(event.target).parents("form").find(".file-group").removeClass("required");
                                }
                            }
                            window.bus && window.bus.emit("achievement:document_type:changed", "#' . $id . '");
                        }
                    ']
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if ($isNew) : ?>
            <div class="row">
                <div class="col-12 ia_fill_component" id="ia_fill_component_<?= $id ?>">
                    <achievement-fill-component :app_id="<?= $application->id ?>" education_search_url="<?= Url::to(['/abiturient/get-education-by-document-type']) ?>" achievement_form_selector="#<?= $id ?>"></achievement-fill-component>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <label class="col-form-label">
                        <?= $model->getAttributeLabel('document_series') ?>
                    </label>
                </div>

                <div class="col-12">
                    <?= Html::input(
                        "text",
                        "IndividualAchievement[document_series]",
                        $model->document_series,
                        [
                            'disabled' => $isReadOnly,
                            'class' => 'form-control',
                            'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries],
                        ]
                    ); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <label class="col-form-label">
                        <?= $model->getAttributeLabel('document_number') ?>
                    </label>
                </div>

                <div class="col-12">
                    <?= Html::input(
                        "text",
                        "IndividualAchievement[document_number]",
                        $model->document_number,
                        [
                            'disabled' => $isReadOnly,
                            'class' => 'form-control',
                            'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber],
                        ]
                    ); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <?php echo ContractorField::widget([
                        'form' => $form,
                        'model' => $model,
                        'disabled' => $isReadOnly,
                        'attribute' => 'contractor_id',
                        'notFoundAttribute' => 'not_found_contractor',
                        'keynum' => $key,
                        'need_subdivision_code' => false,
                        'default_contractor_type_guid_code' => 'contractor_type_ia_guid',
                        'labels' => [
                            'contractor_name' => $model->getAttributeLabel('contractor_id'),
                        ],
                        'options' => [
                            'selectInputId' => "ia-contractor_id_{$key}",
                            'contractorTitleInputId' => "ia-contractor_name_{$key}",
                            'contractorSubdivisionCodeInputId' => "ia-contractor_subdivision_code_{$key}",
                            'contractorLocationCodeInputId' => "ia-contractor_location_code_{$key}",
                            'notFoundCheckboxInputId' => "ia-contractor_not_found_{$key}",
                            'data' => [
                                'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy
                            ],
                        ]
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group required">
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'document_date')
                        ->widget(
                            DatePickerMaskedWidget::class,
                            [
                                'inline' => false,
                                'language' => $appLanguage,
                                'template' => '{input}{addon}',
                                'clientOptions' => [
                                    'clearBtn' => true,
                                    'weekStart' => '1',
                                    'autoclose' => true,
                                    'todayBtn' => 'linked',
                                    'format' => 'dd.mm.yyyy',
                                    'calendarWeeks' => 'true',
                                    'todayHighlight' => 'true',
                                    'orientation' => 'top left',
                                    'endDate' => '-1d',
                                ],
                                'options' => [
                                    'disabled' => $isReadOnly,
                                    'autocomplete' => 'off',
                                    'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::IssuedDate],
                                ],
                                'maskOptions' => ['alias' => 'dd.mm.yyyy']
                            ]
                        ); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'additional')
                        ->textarea([
                            'disabled' => $isReadOnly,
                            'style' => 'min-height: 100px; max-height:200px; min-width:100%;max-width:100%;',
                            'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::Additional],
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group file-group">
            <div class="row">
                <div class="col-12">
                    <?php $attachmentCollection = $model->attachmentCollection; ?>
                    <?= $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                        'attachmentCollection' => $attachmentCollection,
                        'isReadonly' => $isReadOnly,
                        'addNewFile' => !$isReadOnly,
                        'canDeleteFile' => !$isReadOnly,
                        'form' => $form,
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                            'Подпись области прикрепления сканов; в модальном окне ИД на странице ИД: `Скан-копии подтверждающего документа`'
                        ),
                        'model' => $attachmentCollection->getModelEntity()->setRequiredProps(true, Url::to('/abiturient/ia-file-required'), 'document_type_id', '[name="IndividualAchievement[document_type_id]"]')
                    ]); ?>
                </div>

                <div class="col-12">
                    <span class="form-text text-muted">
                        <?= Yii::t(
                            'abiturient/attachment-widget',
                            'Текст сообщения об максимально допустимом размере файла виджета сканов: `Максимальный размер приложенного файла: {uploadMaxFilesizeString}`',
                            ['uploadMaxFilesizeString' => iniGet::getUploadMaxFilesizeString()]
                        ) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!$isReadOnly) : ?>
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <?= Yii::t(
                            'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                            'Подпись кнопки отмены формы; в модальном окне ИД на странице ИД: `Отмена`'
                        ) ?>
                    </button>

                    <?php $tooltipTitle = TooltipWidget::widget([
                        'message' => Yii::$app->configurationManager->getText('save_ia_tooltip'),
                        'params' => 'style="margin-left: 4px;"'
                    ]) ?>
                    <?php $globalTextForSubmitTooltip = Yii::$app->configurationManager->getText('global_text_for_submit_tooltip'); ?>

                    <?= Html::submitButton(
                        $buttonName . $tooltipTitle,
                        [
                            'id' => 'add-ia',
                            'class' => 'btn btn-primary anti-clicker-btn float-right',
                            'data-tooltip_title' => $globalTextForSubmitTooltip,
                        ]
                    ); ?>
                </div>
            </div>
        <?php endif ?>
    </div>
</div>

<?php ActiveForm::end();
