<?php

use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\widgets\ContractorField\ContractorField;
use common\modules\abiturient\models\PassportData;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\web\View;
use yii\widgets\MaskedInput;














$appLanguage = Yii::$app->language;

$documentTypesOptions = [];

$canEdit = $canEdit ?? true;

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
                'abiturient/questionary/parent-modal',
                'Подсказка о том, что выбран архивный тип документа: `Внимание! Выбранный элемент "{attribute}" находится в архиве.`',
                ['attribute' => $model->getAttributeLabel('document_type_id')]
            )
        );
    }
}
?>

<div class="col-12">
    <div class="document-root">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'abiturient/questionary/parent-modal',
                        'Заголовок блока "Паспортные данные родителя" модального окна на странице анкеты поступающего: `Паспортные данные`'
                    ); ?>
                </h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>

                        <?= $form->field($model, 'document_type_id')
                            ->dropDownList($passportTypes, [
                                'disabled' => !$canEdit,
                                'onchange' => "
                                    if (this.value == {$document_type}) {
                                        $('#{$model->clientFormName}-contractor_subdivision_code_{$keynum}').inputmask('999-999');
                                        $('#{$model->clientFormName}-series_{$keynum}').inputmask('9999');
                                        $('#{$model->clientFormName}-number_{$keynum}').inputmask('999999');
                                    } else {
                                        $('#{$model->clientFormName}-contractor_subdivision_code_{$keynum}').inputmask('*{0,100}');
                                        $('#{$model->clientFormName}-series_{$keynum}').inputmask('*{0,100}');
                                        $('#{$model->clientFormName}-number_{$keynum}').inputmask('*{0,100}');
                                    }
                                ",
                                'id' => "{$model->clientFormName}-document_type_id_{$keynum}",
                                'data' => ['document_type_input' => 1],
                                'prompt' => Yii::t(
                                    'abiturient/questionary/parent-modal',
                                    'Подпись пустого значения выпадающего списка для поля "document_type_id" формы родителя на странице анкеты поступающего: `Выберите ...`'
                                )
                            ]); ?>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'series')->widget(
                                    MaskedInput::class,
                                    [
                                        'mask' => $maskSeries,
                                        'options' => [
                                            'disabled' => !$canEdit,
                                            'id' => "{$model->clientFormName}-series_{$keynum}",
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
                                            'disabled' => !$canEdit,
                                            'id' => "{$model->clientFormName}-number_{$keynum}",
                                            'class' => 'form-control',
                                            'data' => [
                                                'one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber
                                            ],
                                        ],
                                    ]
                                ); ?>
                            </div>
                        </div>
                    </div>

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
                                    'disabled' => !$canEdit,
                                    'autocomplete' => 'off',
                                    'id' => "{$model->clientFormName}-issued_date_{$keynum}",
                                    'data' => [
                                        'one-s-attribute-name' => RulesProviderByDocumentType::IssuedDate
                                    ],
                                ],
                                'maskOptions' => ['alias' => 'dd.mm.yyyy']
                            ]
                        );
                        ?>
                    </div>

                    <div class="col-12">
                        <?php echo ContractorField::widget([
                            'form' => $form,
                            'model' => $model,
                            'disabled' => !$canEdit,
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
                            'application' =>  $application ?? null,
                            'options' => [
                                'selectInputId' => "{$model->clientFormName}-contractor_id_{$keynum}",
                                'contractorTitleInputId' => "{$model->clientFormName}-contractor_name_{$keynum}",
                                'contractorSubdivisionCodeInputId' => "{$model->clientFormName}-contractor_subdivision_code_{$keynum}",
                                'contractorLocationCodeInputId' => "{$model->clientFormName}-contractor_location_code_{$keynum}",
                                'notFoundCheckboxInputId' => "{$model->clientFormName}-contractor_not_found_{$keynum}",
                                'data' => [
                                    'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy
                                ]
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>