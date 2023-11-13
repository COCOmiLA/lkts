<?php

use common\components\ini\iniGet;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\Attachment;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredOlympicClassReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicKindReferenceType;
use common\models\dictionary\StoredReferenceType\StoredOlympicProfileReferenceType;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\widgets\ContractorField\ContractorField;
use kartik\form\ActiveForm;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








$appLanguage = Yii::$app->language;

$isReadOnly = !!$model->read_only || !$canEdit;

if (!isset($number)) {
    $number = 'B';
}
if (empty($itemsDoc)) {
    $itemsDoc = [];
}
if (empty($buttonName)) {
    $buttonName = Yii::t(
        'abiturient/bachelor/accounting-benefits/modal-olympiad',
        'Подпись кнопки для сохранения формы; модального окна БВИ на странице льгот: `Добавить`'
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
    $itemsDoc[$model->document_type_id] = $description;

    if ($documentTypesOptions) {
        $model->addError(
            'document_type_id',
            Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-olympiad',
                'Подсказка о том, что выбран архивный тип документа: `Внимание! Выбранный элемент "{attribute}" находится в архиве.`',
                ['attribute' => $model->getAttributeLabel('document_type_id')]
            )
        );
    }
}

$model_id = base64_encode((string)$model->id);
$canDelete = $number !== 'B' ? 'true' : 'false';
$del_url = Url::to(['site/delete-file-benefit']);

$wrapper_id = "olymp-wrapper-{$number}";
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
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label>
                                    <?= Yii::t(
                                        'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                        'Подпись поля "kind" для блока фильтров; модального окна БВИ на странице льгот: `Вид`'
                                    ) ?>
                                </label>

                                <?php $tnStoredOlympicKindReferenceType = StoredOlympicKindReferenceType::tableName(); ?>
                                <?= Select2::widget([
                                    'language' => $appLanguage,
                                    'name' => "kind-filter-{$number}",
                                    'data' => ArrayHelper::map(
                                        StoredOlympicKindReferenceType::find()
                                            ->active()
                                            ->andWhere(["is_folder" => false])
                                            ->orderBy("{$tnStoredOlympicKindReferenceType}.reference_name")
                                            ->all(),
                                        'reference_name',
                                        'reference_name'
                                    ),
                                    'options' => [
                                        'disabled' => $isReadOnly,
                                        'placeholder' => Yii::t(
                                            'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                            'Подпись пустого значения для поля "kind"; модального окна БВИ на странице льгот: `Выберите ...`'
                                        ),
                                        'id' => "kind-filter-{$number}",
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'multiple' => false,
                                        'dropdownParent' => "#{$wrapper_id}",
                                    ],
                                ]); ?>
                            </div>

                            <div class="col-12 col-md-6">
                                <label>
                                    <?= Yii::t(
                                        'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                        'Подпись поля "class" для блока фильтров; модального окна БВИ на странице льгот: `Класс`'
                                    ) ?>
                                </label>

                                <?php $tnStoredOlympicClassReferenceType = StoredOlympicClassReferenceType::tableName(); ?>
                                <?= Select2::widget([
                                    'language' => $appLanguage,
                                    'name' => "class-filter-{$number}",
                                    'data' => ArrayHelper::map(
                                        StoredOlympicClassReferenceType::find()
                                            ->active()
                                            ->andWhere(["is_folder" => false])
                                            ->orderBy("{$tnStoredOlympicClassReferenceType}.reference_name")
                                            ->all(),
                                        'reference_uid',
                                        'reference_name'
                                    ),
                                    'options' => [
                                        'disabled' => $isReadOnly,
                                        'placeholder' => Yii::t(
                                            'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                            'Подпись пустого значения для поля "class"; модального окна БВИ на странице льгот: `Выберите ...`'
                                        ),
                                        'id' => "class-filter-{$number}",
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'multiple' => false,
                                        'dropdownParent' => "#{$wrapper_id}",
                                    ],
                                ]); ?>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="col-12 col-md-6">
                                <label>
                                    <?= Yii::t(
                                        'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                        'Подпись поля "year" для блока фильтров; модального окна БВИ на странице льгот: `Год`'
                                    ) ?>
                                </label>
                                <?= Html::input(
                                    'number',
                                    "year-filter-{$number}",
                                    null,
                                    [
                                        'disabled' => $isReadOnly,
                                        'id' => "year-filter-{$number}",
                                        'class' => 'form-control',
                                    ]
                                ); ?>
                            </div>

                            <div class="col-12 col-md-6">
                                <label>
                                    <?= Yii::t(
                                        'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                        'Подпись поля "profile" для блока фильтров; модального окна БВИ на странице льгот: `Профиль`'
                                    ) ?>
                                </label>

                                <?php $tnStoredOlympicProfileReferenceType = StoredOlympicProfileReferenceType::tableName(); ?>
                                <?= Select2::widget([
                                    'language' => $appLanguage,
                                    'name' => "profile-filter-{$number}",
                                    'data' => ArrayHelper::map(
                                        StoredOlympicProfileReferenceType::find()
                                            ->active()
                                            ->andWhere(["is_folder" => false])
                                            ->orderBy("{$tnStoredOlympicProfileReferenceType}.reference_name")
                                            ->all(),
                                        'reference_uid',
                                        'reference_name'
                                    ),
                                    'options' => [
                                        'disabled' => $isReadOnly,
                                        'placeholder' => Yii::t(
                                            'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                            'Подпись пустого значения для поля "profile"; модального окна БВИ на странице льгот: `Выберите ...`'
                                        ),
                                        'id' => "profile-filter-{$number}",
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'multiple' => false,
                                        'dropdownParent' => "#{$wrapper_id}",
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'olympiad_id')
                    ->widget(
                        DepDrop::class,
                        [
                            'language' => $appLanguage,
                            'type' => DepDrop::TYPE_SELECT2,
                            'options' => [
                                'disabled' => $isReadOnly,
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись пустого значения для поля "olympiad_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                                ),
                                'id' => "first_drop_oly_{$number}"
                            ],
                            'select2Options' => ['pluginOptions' => [
                                'allowClear' => true,
                                'multiple' => false,
                                'dropdownParent' => "#{$wrapper_id}",
                            ]],
                            'data' => $items,
                            'pluginOptions' => [
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись пустого значения для поля "olympiad_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                                ),
                                'loadingText' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись загружаемого значения для поля "olympiad_id"; модального окна БВИ на странице льгот: `Загрузка ...`'
                                ),
                                'depends' => [
                                    "kind-filter-{$number}",
                                    "class-filter-{$number}",
                                    "year-filter-{$number}",
                                    "profile-filter-{$number}",
                                ],
                                'url' => Url::to(['/site/filter-olympiads', 'app_id' => $id]),
                            ]
                        ]
                    ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'special_mark_id')
                    ->widget(
                        DepDrop::class,
                        [
                            'language' => $appLanguage,
                            'type' => DepDrop::TYPE_SELECT2,
                            'options' => [
                                'disabled' => $isReadOnly,
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись пустого значения для поля "special_mark_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                                ),
                                'id' => "second_drop_oly_{$number}"
                            ],
                            'select2Options' => ['pluginOptions' => [
                                'allowClear' => true,
                                'multiple' => false,
                                'dropdownParent' => "#{$wrapper_id}",
                            ]],
                            'data' => $itemsOlymp,
                            'pluginOptions' => [
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись пустого значения для поля "special_mark_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                                ),
                                'loadingText' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись загружаемого значения для поля "special_mark_id"; модального окна БВИ на странице льгот: `Загрузка ...`'
                                ),
                                'depends' => ["first_drop_oly_{$number}"],
                                'url' => Url::to(['site/olymp-type', 'app_id' => $id, 'id' => $model->id]),
                            ]
                        ]
                    ); ?>
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
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись пустого значения для поля "document_type_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                                ),
                                'id' => "third_drop_oly_{$number}",
                                'data' => ['document_type_input' => 1],
                            ],
                            'select2Options' => ['pluginOptions' => [
                                'allowClear' => true,
                                'multiple' => false,
                                'dropdownParent' => "#{$wrapper_id}",
                            ]],
                            'data' => $itemsDoc,
                            'pluginOptions' => [
                                'placeholder' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись пустого значения для поля "document_type_id"; модального окна БВИ на странице льгот: `Выберите ...`'
                                ),
                                'loadingText' => Yii::t(
                                    'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                    'Подпись загружаемого значения для поля "document_type_id"; модального окна БВИ на странице льгот: `Загрузка ...`'
                                ),
                                'depends' => ["second_drop_oly_{$number}"],
                                'url' => Url::to(['site/doc-type-olympiads', 'id' => $id]),
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
                    'default_contractor_type_guid_code' => 'contractor_type_olymp_guid',
                    'labels' => [
                        'contractor_name' => $model->getAttributeLabel('contractor_id'),
                    ],
                    'application' => $application,
                    'options' => [
                        'selectInputId' => "olympiad-contractor_id_{$number}",
                        'contractorTitleInputId' => "olympiad-contractor_name_{$number}",
                        'contractorSubdivisionCodeInputId' => "olympiad-contractor_subdivision_code_{$number}",
                        'contractorLocationCodeInputId' => "olympiad-contractor_location_code_{$number}",
                        'notFoundCheckboxInputId' => "olympiad-contractor_not_found_{$number}",
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
                        'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries],
                        'disabled' => $isReadOnly,
                    ]); ?>
            </div>

            <div class="col-12 col-md-4">
                <?= $form->field($model, 'document_number')
                    ->textInput([
                        'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber],
                        'disabled' => $isReadOnly,
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
                                'endDate' => '-1d',
                                'weekStart' => '1',
                                'autoclose' => true,
                                'todayBtn' => 'linked',
                                'format' => 'dd.mm.yyyy',
                                'calendarWeeks' => 'true',
                                'todayHighlight' => 'true',
                                'orientation' => 'top left',
                            ],
                            'options' => [
                                'disabled' => $isReadOnly,
                                'autocomplete' => 'off',
                                'id' => "dp_ol_{$number}",
                                'data' => ['one-s-attribute-name' => RulesProviderByDocumentType::IssuedDate],
                            ],
                            'maskOptions' => ['alias' => 'dd.mm.yyyy']
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
                            'form' => $form,
                            'isReadonly' => $isReadOnly,
                            'addNewFile' => !$isReadOnly,
                            'canDeleteFile' => !$isReadOnly,
                            'required' => $file_required,
                            'attachmentCollection' => $model->attachmentCollection,
                            'label' => Yii::t(
                                'abiturient/bachelor/accounting-benefits/modal-olympiad',
                                'Подпись области прикрепления сканов; модального окна БВИ на странице льгот: `Скан-копии подтверждающего документа`'
                            ),
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
            <?= $form->field($model, 'id_application')->label('')->hiddenInput(['value' => $id]); ?>

            <?php if (isset($model->id)) : ?>
                <?= $form->field($model, 'id')->label('')->hiddenInput(['value' => $model->id]); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php ActiveForm::end();
