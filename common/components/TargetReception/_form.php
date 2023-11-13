<?php

use common\components\ini\iniGet;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\Attachment;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\widgets\ContractorField\ContractorField;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








$appLanguage = Yii::$app->language;

$isReadOnly = !!$model->read_only || !$canEdit;

if (!isset($number)) {
    $number = 'X';
}
if (empty($buttonName)) {
    $buttonName = Yii::t(
        'abiturient/bachelor/accounting-benefits/modal-target-reception',
        'Подпись кнопки для сохранения формы; модального окна целевых договоров на странице льгот: `Добавить`'
    );
}
if (empty($action)) {
    $action = [];
}
if (empty($items)) {
    $items = [];
}

$canDelete = $number !== 'B' ? 'true' : 'false';
$del_url = Url::to(['site/delete-file-target']);


if ($model->documentType && !isset($items[$model->documentType->id])) {
    $items[$model->documentType->id] = $model->documentType->description;
}

?>

<?php $form = ActiveForm::begin([
    'method' => 'post',
    'action' => $action
]); ?>

<div class="document-root">
    <?php if (!$model->isNewRecord) : ?>
        <?= $this->render(
            '@abiturientViews/_document_check_status_render',
            compact(['model'])
        ) ?>
    <?php endif ?>

    <div class="row">
        <div class="col-12">
            <?php echo ContractorField::widget([
                'form' => $form,
                'model' => $model,
                'disabled' => $isReadOnly,
                'attribute' => 'target_contractor_id',
                'notFoundAttribute' => 'not_found_target_contractor',
                'keynum' => $number,
                'need_subdivision_code' => false,
                'default_contractor_type_guid_code' => 'contractor_type_target_reception_guid',
                'labels' => [
                    'contractor_name' => $model->getAttributeLabel('target_contractor_id'),
                ],
                'application' => $application,
                'options' => [
                    'selectInputId' => "target_reception-target_contractor_id_{$number}",
                    'contractorTitleInputId' => "target_reception-target_contractor_name_{$number}",
                    'contractorSubdivisionCodeInputId' => "target_reception-target_contractor_subdivision_code_{$number}",
                    'contractorLocationCodeInputId' => "target_reception-target_contractor_location_code_{$number}",
                    'notFoundCheckboxInputId' => "target_reception-target_contractor_not_found_{$number}",
                    'contractorFormName' => 'TargetContractor',
                    'contractorApproveSelectId' => "target_reception-target_contractor_approve_{$number}",
                ]
            ]); ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'document_type_id')
                ->dropDownList(
                    $items,
                    [
                        'disabled' => $isReadOnly,
                        'prompt' => Yii::t(
                            'abiturient/bachelor/accounting-benefits/modal-target-reception',
                            'Подпись пустого значения для поля "document_type_id"; модального окна целевых договоров на странице льгот: `Выберите ...`'
                        ),
                        'id' => "doc_type_{$number}",
                        'data' => [
                            'document_type_input' => 1
                        ],
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
                'attribute' => 'document_contractor_id',
                'notFoundAttribute' => 'not_found_document_contractor',
                'keynum' => $number,
                'need_subdivision_code' => false,
                'default_contractor_type_guid_code' => 'contractor_type_target_reception_guid',
                'labels' => [
                    'contractor_name' => $model->getAttributeLabel('document_contractor_id'),
                ],
                'application' => $application,
                'options' => [
                    'selectInputId' => "target_reception-document_contractor_id_{$number}",
                    'contractorTitleInputId' => "target_reception-document_contractor_name_{$number}",
                    'contractorSubdivisionCodeInputId' => "target_reception-document_contractor_subdivision_code_{$number}",
                    'contractorLocationCodeInputId' => "target_reception-document_contractor_location_code_{$number}",
                    'notFoundCheckboxInputId' => "target_reception-document_contractor_not_found_{$number}",
                    'contractorFormName' => 'DocumentContractor',
                    'contractorApproveSelectId' => "target_reception-document_contractor_approve_{$number}",
                    'data' => [
                        'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy
                    ]
                ]
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-4">
            <?= $form->field($model, 'document_series')
                ->textInput([
                    'disabled' => $isReadOnly,
                    'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries],
                ]); ?>
        </div>

        <div class="col-12 col-md-4">
            <?= $form->field($model, 'document_number')
                ->textInput([
                    'disabled' => $isReadOnly,
                    'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber],
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
                            'id' => "dp_tg_{$number}",
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
            <?php
            $file_required = $model->attachmentCollection->isRequired();
            ?>
            <div class="form-group <?= $file_required ? 'required' : '' ?>">
                <?= $this->render(
                    '@abiturient/views/partial/fileInput/_fileInput',
                    [
                        'attachmentCollection' => $model->attachmentCollection,
                        'required' => $file_required,
                        'isReadonly' => $isReadOnly,
                        'addNewFile' => !$isReadOnly,
                        'canDeleteFile' => !$isReadOnly,
                        'form' => $form,
                        'label' => Yii::t(
                            'abiturient/bachelor/accounting-benefits/modal-target-reception',
                            'Подпись области прикрепления сканов; модального окна целевых договоров на странице льгот: `Скан-копии подтверждающего документа`'
                        )
                    ]
                ); ?>
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
                ['class' => 'form-text text-muted', 'style' => 'padding-left: 0;']
            ); ?>

            <?= Html::tag(
                'span',
                Yii::t(
                    'abiturient/attachment-widget',
                    'Текст перечисляющий доступные форматов для сканов: `Список допустимых форматов файлов: {extensions}`',
                    ['extensions' => Attachment::getExtensionsListForRules()]
                ),
                ['class' => 'form-text text-muted', 'style' => 'padding-left: 0;']
            ); ?>
        </div>
    </div>

    <?php if (!$isReadOnly) : ?>
        <div class="row">
            <div class="col-12">
                <?php $globalTextForSubmitTooltip = Yii::$app->configurationManager->getText('global_text_for_submit_tooltip', $application->type ?? null); ?>
                <?= Html::submitButton($buttonName, [
                    'data-tooltip_title' => $globalTextForSubmitTooltip,
                    'class' => 'btn btn-primary float-right anti-clicker-btn'
                ]); ?>
            </div>
        </div>
    <?php endif ?>

    <div class="hidden m-n3">
        <?= $form->field($model, 'id_application')
            ->hiddenInput(['value' => $id])
            ->label(''); ?>

        <?php if (isset($model->id)) : ?>
            <?= $form->field($model, 'id')
                ->hiddenInput(['value' => $model->id])
                ->label(''); ?>
        <?php endif; ?>
    </div>

</div>
<?php ActiveForm::end();
