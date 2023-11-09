<?php

use common\components\ini\iniGet;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\Attachment;
use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\widgets\ContractorField\ContractorField;
use kartik\form\ActiveForm;
use kartik\widgets\DepDrop;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








$appLanguage = Yii::$app->language;

$isReadOnly = !!$model->read_only || !$canEdit;

if (!isset($number)) {
    $number = 'A';
}
if (empty($itemsDoc)) {
    $itemsDoc = [];
}
if (empty($buttonName)) {
    $buttonName = Yii::t(
        'abiturient/bachelor/accounting-benefits/modal-benefits',
        'Подпись кнопки для сохранения формы; модального окна льгот на странице льгот: `Добавить`'
    );
}
if (empty($action)) {
    $action = [];
}
if ($model->document_type_id && !isset($itemsDoc[$model->document_type_id])) {
    [
        'description' => $description,
        'documentTypesOptions' => $documentTypesOptions,
    ] = DocumentType::processArchiveDocForDropdown('id', $model->document_type_id);
    $document_types[$model->document_type_id] = $description;

    if ($documentTypesOptions) {
        $model->addError(
            'document_type_id',
            Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-benefits',
                'Подсказка о том, что выбран архивный тип документа: `Внимание! Выбранный элемент "{attribute}" находится в архиве.`',
                ['attribute' => $model->getAttributeLabel('document_type_id')]
            )
        );
    }
}
$model_id = base64_encode((string)$model->id);
$canDelete = $number !== 'B' ? 'true' : 'false';
$del_url = Url::to(['site/delete-file-benefit']);
$has_pending = $has_pending ?? false;
$wrapper_id = "benefit-wrapper-{$number}";

?>

<?php $form = ActiveForm::begin([
    'method' => 'post',
    'action' => $action
]); ?>

<div class="document-root">
    <div id="<?php echo $wrapper_id ?>">
        <?php if (!$model->isNewRecord) : ?>
            <?= $this->render(
                '@abiturientViews/_document_check_status_render',
                compact(['model'])
            ) ?>
        <?php endif ?>

        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'code')
                    ->dropDownList($items, [
                        'disabled' => $isReadOnly,
                        'prompt' => Yii::t(
                            'abiturient/bachelor/accounting-benefits/modal-benefits',
                            'Подпись пустого значения для поля "code"; модального окна льгот на странице льгот: `Выберите ...`'
                        ),
                        'id' => $isReadOnly ? "not_lgot_id_{$number}" : "lgot_id_{$number}",
                        'class' => 'lgot-code',
                        'data-number' => $number
                    ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'document_type_id')
                    ->widget(
                        DepDrop::class,
                        [
                            'language' => $appLanguage,
                            'type' => DepDrop::TYPE_SELECT2,
                            'options' => [
                                'disabled' => $isReadOnly,
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-benefits',
                                    'Подпись пустого значения для поля "document_type_id"; модального окна льгот на странице льгот: `Выберите ...`'
                                ),
                                'id' => "second_drop_{$number}",
                                'data' => [
                                    'document_type_input' => 1
                                ],
                            ],
                            'select2Options' => [
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'multiple' => false,
                                    'dropdownParent' => "#{$wrapper_id}",
                                ]
                            ],
                            'data' => $itemsDoc,
                            'pluginOptions' => [
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-benefits',
                                    'Подпись пустого значения для поля "document_type_id"; модального окна льгот на странице льгот: `Выберите ...`'
                                ),
                                'loadingText' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-benefits',
                                    'Подпись загружаемого значения для поля "document_type_id"; модального окна льгот на странице льгот: `Загрузка ...`'
                                ),
                                'depends' => ["lgot_id_{$number}"],
                                'url' => Url::to(['site/doc-type', 'id' => $id]),
                            ]
                        ]
                    ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-4">
                <?= $form->field($model, 'document_series')
                    ->textInput([
                        'disabled' => $isReadOnly,
                        'data' => [
                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries
                        ],
                    ]); ?>
            </div>

            <div class="col-12 col-md-4">
                <?= $form->field($model, 'document_number')
                    ->textInput([
                        'disabled' => $isReadOnly,
                        'data' => [
                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber
                        ],
                    ]); ?>
            </div>

            <div class="col-12 col-md-4">
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
                                'id' => "dp_bf_{$number}",
                                'data' => [
                                    'one-s-attribute-name' => RulesProviderByDocumentType::IssuedDate
                                ],
                            ],
                            'maskOptions' => [
                                'alias' => 'dd.mm.yyyy'
                            ]
                        ]
                    ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php echo ContractorField::widget([
                    'form' => $form,
                    'model' => $model,
                    'disabled' => $isReadOnly,
                    'attribute' => 'contractor_id',
                    'notFoundAttribute' => 'notFoundContractor',
                    'keynum' => $number,
                    'need_subdivision_code' => false,
                    'default_contractor_type_guid_code' => 'contractor_type_pref_guid',
                    'labels' => [
                        'contractor_name' => $model->getAttributeLabel('contractor_id'),
                    ],
                    'need_approve' => $has_pending,
                    'application' => $application,
                    'options' => [
                        'selectInputId' => "benefit-contractor_id_{$number}",
                        'contractorTitleInputId' => "benefit-contractor_name_{$number}",
                        'contractorSubdivisionCodeInputId' => "benefit-contractor_subdivision_code_{$number}",
                        'contractorLocationCodeInputId' => "benefit-contractor_location_code_{$number}",
                        'notFoundCheckboxInputId' => "benefit-contractor_not_found_{$number}",
                        'data' => [
                            'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy
                        ]
                    ]
                ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php $file_required = $model->attachmentCollection->isRequired(); ?>
                <div class="form-group <?= $file_required ? 'required' : '' ?>">
                    <?= $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                        'attachmentCollection' => $model->attachmentCollection,
                        'isReadonly' => $isReadOnly,
                        'required' => $file_required,
                        'form' => $form,
                        'addNewFile' => !$isReadOnly,
                        'canDeleteFile' => !$isReadOnly,
                        'label' => Yii::t(
                            'abiturient/bachelor/accounting-benefits/modal-benefits',
                            'Подпись области прикрепления сканов; модального окна льгот на странице льгот: `Скан-копии подтверждающего документа`'
                        ),
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 ml-auto">
                <?= Html::tag(
                    'span',
                    Yii::t(
                        'abiturient/attachment-widget',
                        'Текст сообщения об максимально допустимом размере файла виджета сканов: `Максимальный размер приложенного файла: {uploadMaxFilesizeString}`',
                        ['uploadMaxFilesizeString' => iniGet::getUploadMaxFilesizeString()]
                    ),
                    ['class' => 'form-text text-muted', 'style' => 'padding-left: 0px;']
                ); ?>

                <?= Html::tag(
                    'span',
                    Yii::t(
                        'abiturient/attachment-widget',
                        'Текст перечисляющий доступные форматов для сканов: `Список допустимых форматов файлов: {extensions}`',
                        ['extensions' => Attachment::getExtensionsListForRules()]
                    ),
                    ['class' => 'form-text text-muted', 'style' => 'padding-left: 0px;']
                ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-5">
                <?= $form->field($model, 'priority_right')
                    ->checkbox([
                        'id' => "specific_law_{$number}",
                        'disabled' => $isReadOnly,
                    ]); ?>
            </div>

            <div class="col-12 col-md-5">
                <?= $form->field($model, 'individual_value')
                    ->checkbox([
                        'id' => "concession_low_{$number}",
                        'disabled' => $isReadOnly,
                    ]); ?>
            </div>

            <?php if (!$isReadOnly) : ?>
                <div class="col-12 d-flex justify-content-end align-items-end">
                    <?php $globalTextForSubmitTooltip = Yii::$app->configurationManager->getText('global_text_for_submit_tooltip', $application->type ?? null); ?>
                    <?= Html::submitButton($buttonName, [
                        'data-tooltip_title' => $globalTextForSubmitTooltip,
                        'class' => 'btn btn-primary float-right anti-clicker-btn'
                    ]); ?>
                </div>
            <?php endif ?>
        </div>

        <div class="hidden m-n3">
            <?= $form->field($model, 'id_application')->label('')
                ->hiddenInput(['value' => $id]); ?>

            <?php if (isset($model->id)) : ?>
                <?= $form->field($model, 'id')->label('')
                    ->hiddenInput(['value' => $model->id]); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php ActiveForm::end();
