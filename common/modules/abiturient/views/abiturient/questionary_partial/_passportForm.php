<?php

use common\components\ini\iniGet;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\EmptyCheck;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\PassportData;
use common\widgets\ContractorField\ContractorField;
use kartik\form\ActiveForm;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\bootstrap4\Alert;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\MaskedInput;













$appLanguage = Yii::$app->language;

$documentTypesOptions = [];

$isReadonly = $isReadonly ?? false;
$allowAddNewFileToOldPassportAfterApprove = $allowAddNewFileToOldPassportAfterApprove ?? false;
$allowDeleteFileFromOldPassportAfterApprove = $allowDeleteFileFromOldPassportAfterApprove ?? false;
$show_file_input = $show_file_input ?? true;


$isReadonly = !$model->convertFlagAccordingDocumentStatus(!$isReadonly);
$allowAddNewFileToOldPassportAfterApprove = $model->convertFlagAccordingDocumentStatus($allowAddNewFileToOldPassportAfterApprove);
$allowDeleteFileFromOldPassportAfterApprove = $model->convertFlagAccordingDocumentStatus($allowDeleteFileFromOldPassportAfterApprove);

if (!$model->document_type_id) {
    $model->document_type_id = $document_type;
}

$maskSeries = '*{0,100}';
$maskNumber = '*{0,100}';
$maskCode = '*{0,100}';

if ($model->document_type_id != null && $model->document_type_id == $document_type) {
    $maskSeries = '9999';
    $maskNumber = '999999';
    $maskCode = '999-999';
}

if ($model->document_type_id && !isset($passportTypes[$model->document_type_id])) {
    [
        'description' => $description,
        'documentTypesOptions' => $documentTypesOptions,
    ] = DocumentType::processArchiveDocForDropdown('id', $model->document_type_id);
    $passportTypes[$model->document_type_id] = $description;

    if ($documentTypesOptions) {
        $model->addError(
            'document_type_id',
            Yii::t(
                'abiturient/questionary/passport-modal',
                'Подсказка о том, что выбран архивный тип документа: `Внимание! Выбранный элемент "{attribute}" находится в архиве.`',
                ['attribute' => $model->getAttributeLabel('document_type_id')]
            )
        );
    }
}

$use_own_form = false;
$formName = $model->clientFormName;
if (!isset($form)) {
    $use_own_form = true;
    $form = ActiveForm::begin(['id' => 'form-passport' . $model->id, 'action' => $action, 'options' => ['class' => 'passport-form document-form']]);
} ?>

<div class="document-root mt-n3">
    <?php echo $form->field($model, 'id')
        ->hiddenInput()
        ->label(false); ?>

    <?php if (!$model->isNewRecord) : ?>
        <?= $this->render(
            '@abiturientViews/_document_check_status_render',
            compact(['model'])
        ) ?>
    <?php endif ?>

    <div class="row">
        <div class="col-12">
            <?php $alertMessage = Yii::$app->configurationManager->getText('info_message_in_questionary_for_passport_form');
            if (!EmptyCheck::isEmpty($alertMessage)) {
                echo Alert::widget([
                    'options' => ['class' => 'alert-info'],
                    'body' => $alertMessage,
                ]);
            } ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?= $form->field($model, 'document_type_id')
                ->dropDownList($passportTypes, [
                    'disabled' => $isReadonly,
                    'options' => $documentTypesOptions,
                    'onchange' => "
                        if (this.value == {$document_type}) {
                            $('#{$formName}-contractor_subdivision_code_{$keynum}').inputmask('999-999');
                            $('#{$formName}-series_{$keynum}').inputmask('9999');
                            $('#{$formName}-number_{$keynum}').inputmask('999999');
                        } else {
                            $('#{$formName}-contractor_subdivision_code_{$keynum}').inputmask('*{0,100}');
                            $('#{$formName}-series_{$keynum}').inputmask('*{0,100}');
                            $('#{$formName}-number_{$keynum}').inputmask('*{0,100}');
                        }
                    ",
                    'id' => "{$formName}-document_type_id_{$keynum}",
                    'data' => ['document_type_input' => 1],
                    'prompt' => Yii::t(
                        'abiturient/questionary/passport-modal',
                        'Подпись пустого значения выпадающего списка для поля "document_type_id" формы модального окна паспорта на странице анкеты поступающего: `Выберите ...`'
                    ),
                ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'series')->widget(
                MaskedInput::class,
                [
                    'mask' => $maskSeries,
                    'options' => [
                        'disabled' => $isReadonly,
                        'id' => "{$formName}-series_{$keynum}",
                        'class' => 'form-control',
                        'data' => [
                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries
                        ],
                    ],

                ]
            ); ?>
        </div>

        <div class="col-12 col-md-6">
            <?= $form->field($model, 'number')->widget(
                MaskedInput::class,
                [
                    'mask' => $maskNumber,
                    'options' => [
                        'disabled' => $isReadonly,
                        'id' => "{$formName}-number_{$keynum}",
                        'class' => 'form-control',
                        'data' => [
                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber
                        ],
                    ],
                ]
            ); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?= $form->field($model, 'issued_date')->widget(
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
                        'disabled' => $isReadonly,
                        'autocomplete' => 'off',
                        'id' => "{$formName}-issued_date_{$keynum}",
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
                'disabled' => $isReadonly,
                'attribute' => 'contractor_id',
                'notFoundAttribute' => 'notFoundContractor',
                'keynum' => $keynum,
                'need_subdivision_code' => true,
                'labels' => [
                    'contractor_name' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "issued_by" формы "Паспортные данные": `Кем выдан`'),
                ],
                'contractor_type_ref_uid' => StoredContractorTypeReferenceType::findByUID(
                    Yii::$app->configurationManager->getCode('contractor_type_ufms_guid')
                )->reference_uid,
                'mask_subdivision_code' => $maskCode,
                'application' => $application ?? null,
                'options' => [
                    'selectInputId' => "{$formName}-contractor_id_{$keynum}",
                    'contractorTitleInputId' => "{$formName}-contractor_name_{$keynum}",
                    'contractorSubdivisionCodeInputId' => "{$formName}-contractor_subdivision_code_{$keynum}",
                    'contractorLocationCodeInputId' => "{$formName}-contractor_location_code_{$keynum}",
                    'notFoundCheckboxInputId' => "{$formName}-contractor_not_found_{$keynum}",
                    'data' => [
                        'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy
                    ]
                ]
            ]); ?>
        </div>
    </div>

    <?php if ($show_file_input) : ?>
        <div class="row">
            <div class="col-12">
                <?php $file_required = $model->attachmentCollection->isRequired(); ?>
                <div class="form-group <?= $file_required ? 'required' : '' ?>">
                    <?= $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                        'attachmentCollection' => $model->attachmentCollection,
                        'required' => $file_required,
                        'isReadonly' => !(!$isReadonly || $allowAddNewFileToOldPassportAfterApprove || $allowDeleteFileFromOldPassportAfterApprove),
                        'addNewFile' => !($isReadonly && !$allowAddNewFileToOldPassportAfterApprove),
                        'canDeleteFile' => !($isReadonly && !$allowDeleteFileFromOldPassportAfterApprove),
                        'form' => $form,
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                            'Подпись области прикрепления сканов; в модальном окне ИД на странице ИД: `Скан-копии подтверждающего документа`'
                        ),
                    ]); ?>
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
    <?php endif; ?>
</div>

<?php if ($use_own_form) : ?>
    <?php if (!$isReadonly || $allowAddNewFileToOldPassportAfterApprove || $allowDeleteFileFromOldPassportAfterApprove) : ?>
        <div class="row">
            <div class="col-12">
                <?= Html::button(
                    Yii::t(
                        'abiturient/questionary/passport-modal',
                        'Подпись кнопки для сохранения формы модального окна паспорта на странице анкеты поступающего: `Сохранить`'
                    ),
                    [
                        'id' => "{$formName}-submit_btn" . $model->id,
                        'class' => 'btn btn-primary float-right btn-save-passport anti-clicker-btn submit-emulation',
                        'data' => ['id' => $model->id]
                    ]
                ); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>
<?php endif;
