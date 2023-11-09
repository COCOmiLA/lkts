<?php

use common\components\ini\iniGet;
use common\models\dictionary\EducationType;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\EmptyCheck;
use common\modules\abiturient\assets\educationAsset\EducationModalAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\widgets\ContractorField\ContractorField;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\DepDrop;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\MaskedInput;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use kartik\select2\Select2;














EducationModalAsset::register($this);

$addNewFile = $addNewFile ?? true;
$canDeleteFile = $canDeleteFile ?? true;
$hideProfileFieldForEducation = isset($hideProfileFieldForEducation) ? $hideProfileFieldForEducation : false;


$canEdit = $education_data->convertFlagAccordingDocumentStatus($canEdit);
$addNewFile = $education_data->convertFlagAccordingDocumentStatus($addNewFile);
$canDeleteFile = $education_data->convertFlagAccordingDocumentStatus($canDeleteFile);

$appLanguage = Yii::$app->language;

$isModer = Yii::$app->user->identity->isModer();
$antiClickerClass = 'anti-clicker-btn';
if ($isModer) {
    $antiClickerClass = '';
    $has_pending_contractor = $has_pending_contractor ?? false;
} else {
    $has_pending_contractor = false;
}

$template = "{input}\n{error}";
Modal::begin([
    'title' => Html::tag('h4', $title),
    'size' => Modal::SIZE_LARGE,
    'id' => $modal_id,
    'options' => [
        'tabindex' => false,
        'class' => 'education-modal',
    ],
]);

if (!isset($data_attributes)) {
    $data_attributes = [];
}
$form = ActiveForm::begin([
    'options' => [
        'class' => 'form-horizontal education-save-form document-root',
        'data' => ArrayHelper::merge(['modal-to-close' => $modal_id], $data_attributes)
    ],
    'fieldConfig' => ['template' => "{input}\n{error}"],
    'action' => [
        '/bachelor/save-education',
        'app_id' => $application->id,
        'edu_id' => $education_data->id
    ]
]);

$wrapper_id = "education-data-wrapper-{$postfix}";
?>

<div id="<?php echo $wrapper_id ?>">
    <div class="alert alert-danger error-presenter" style="display: none" role="alert"></div>

    <?php if (!$education_data->isNewRecord) : ?>
        <div class="row">
            <div class="col-12">
                <?= $this->render(
                    '@abiturientViews/_document_check_status_render',
                    ['model' => $education_data]
                ) ?>
            </div>
        </div>
    <?php endif ?>

    <?php if (
        $education_data->original_from_epgu &&
        $textForOriginalEpguOnEducationData = Yii::$app->configurationManager->getText('text_for_original_epgu_on_education_data')
    ) : ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= $textForOriginalEpguOnEducationData ?>

            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($alertMessage = Yii::$app->configurationManager->getText('alert_message_in_education_for_form')) : ?>
        <div class="row">
            <div class="col-12">
                <?= Alert::widget([
                    'options' => ['class' => 'alert-info'],
                    'body' => $alertMessage,
                ]); ?>
            </div>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group required">
                <div class="row">
                    <label class="col-sm-4 col-12 control-label has-star">
                        <?= $education_data->getAttributeLabel('education_type_id'); ?>
                    </label>

                    <div class="col-sm-8 col-12">
                        <?php $edutype_uid = Yii::$app->configurationManager->getCode('edu_type_guid');
                        $edutype_id = null;
                        if ($edutype_uid) {
                            $edutype_id = EducationType::findByUID($edutype_uid)->id ?? null;
                        }
                        $educationTypes = EducationData::getEducationTypeList();
                        $education_data->education_type_id == null ? $edutype_selected = $edutype_id : $edutype_selected = $education_data->education_type_id;
                        
                        if ($education_data->education_type_id && !array_key_exists($education_data->education_type_id, $educationTypes)) {
                            $educationTypes[$education_data->education_type_id] = $education_data->educationType->description;
                        } ?>

                        <?= $form->field($education_data, 'education_type_id')
                            ->label(false)
                            ->widget(Select2::class, [
                                'language' => Yii::$app->language,
                                'data' => $educationTypes,
                                'options' => [
                                    $edutype_selected => ['Selected' => true],
                                    'id' => 'educationdata-education_type_id' . $postfix,
                                    'placeholder' => Yii::t(
                                        'abiturient/bachelor/education/education-modal',
                                        'Подпись для пустого поля "education_type_id"; модального окна обработки образования на странице док. об образ.: `Выберите ...`'
                                    )
                                ],
                                'pluginOptions' => [
                                    'dropdownParent' => "#{$wrapper_id}",
                                ],
                                'readonly' => !$canEdit,
                                'disabled' => !$canEdit,
                            ]); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label class="col-sm-4 col-12 control-label">
                        <?= $education_data->getAttributeLabel('education_level_id'); ?>
                    </label>

                    <div class="col-sm-8 col-12">
                        <?= $form->field($education_data, 'education_level_id')->widget(
                            DepDrop::class,
                            [
                                'language' => $appLanguage,
                                'class' => 'form-control',
                                'options' => ['placeholder' => false, 'id' => 'educationdata-education_level_id' . $postfix],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => [
                                    'pluginOptions' => [
                                        'allowClear' => false,
                                        'multiple' => false,
                                        'dropdownParent' => "#{$wrapper_id}",
                                    ],
                                ],
                                'readonly' => !$canEdit,
                                'disabled' => !$canEdit,
                                'pluginOptions' => [
                                    'depends' => ['educationdata-education_type_id' . $postfix],
                                    'initDepends' => ['educationdata-education_type_id' . $postfix],
                                    'params' => ['educationdata-education_type_id' . $postfix],
                                    'initialize' => true,
                                    'placeholder' => false,
                                    'url' => Url::to(['/bachelor/edu-levels']),
                                    'loadingText' => Yii::t(
                                        'abiturient/bachelor/education/education-modal',
                                        'Подпись загружающегося поля "education_level_id"; модального окна обработки образования на странице док. об образ.: `Загрузка ...`'
                                    ),
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <div class="form-group required">
                <div class="row">
                    <label class="col-sm-4 col-12 control-label has-star">
                        <?= $education_data->getAttributeLabel('document_type_id'); ?>
                    </label>

                    <div class="col-sm-8 col-12">
                        <?= $form->field($education_data, 'document_type_id')->widget(
                            DepDrop::class,
                            [
                                'language' => $appLanguage,
                                'class' => 'form-control',
                                'options' => [
                                    'placeholder' => false,
                                    'id' => 'educationdata-document_type_id' . $postfix,
                                    'data' => [
                                        'document_type_input' => 1
                                    ],
                                ],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => [
                                    'pluginOptions' => [
                                        'allowClear' => false,
                                        'multiple' => false,
                                        'dropdownParent' => "#{$wrapper_id}",
                                    ]
                                ],
                                'readonly' => !$canEdit,
                                'disabled' => !$canEdit,
                                'pluginOptions' => [
                                    'depends' => ['educationdata-education_type_id' . $postfix, 'educationdata-education_level_id' . $postfix],
                                    'params' => ['educationdata-education_type_id' . $postfix, 'educationdata-education_level_id' . $postfix],
                                    'initDepends' => ['educationdata-education_level_id' . $postfix],
                                    'initialize' => true,
                                    'placeholder' => false,
                                    'url' => Url::to(['/bachelor/edu-docs']),
                                    'loadingText' => Yii::t(
                                        'abiturient/bachelor/education/education-modal',
                                        'Подпись загружающегося поля "document_type_id"; модального окна обработки образования на странице док. об образ.: `Загрузка ...`'
                                    ),
                                ],
                                'pluginEvents' => [
                                    'change' => '
                                        function() {
                                            var code = $("#educationdata-document_type_id' . $postfix . '")
                                                .children("option")
                                                .filter(":selected")
                                                .data("code");
                                            if(code == "' . Yii::$app->configurationManager->getCode('edu_certificate_doc_type_guid') . '") {
                                                $("#educationdata-series' . $postfix . '").inputmask({"mask":"[-]*{0,5}"});
                                                $("#educationdata-number' . $postfix . '").inputmask({"mask":"*{0,14}"});
                                            } else if(code == "' . Yii::$app->configurationManager->getCode('bak_doc_guid') . '" 
                                                    || code == "' . Yii::$app->configurationManager->getCode('mag_doc_guid') . '"
                                                    || code == "' . Yii::$app->configurationManager->getCode('spec_doc_guid') . '"
                                            ) {
                                                $("#educationdata-series' . $postfix . '").inputmask({"mask":"[-]*{0,6}"});
                                                $("#educationdata-number' . $postfix . '").inputmask({"mask":"*{0,8}"});
                                            } else {' .  '
                                                $("#educationdata-series' . $postfix . '").inputmask("remove");
                                                $("#educationdata-number' . $postfix . '").inputmask("remove");
                                                $("#educationdata-series' . $postfix . '")
                                                    .closest(".field-educationdata-series")
                                                    .removeClass("has-error");
                                                $("#educationdata-series' . $postfix . '")
                                                    .siblings(".field-educationdata-series")
                                                    .removeClass("has-error");
                                                $("#educationdata-series' . $postfix . '")
                                                    .closest(".field-educationdata-series .form-text text-muted")
                                                    .remove();
                                                $("#educationdata-series' . $postfix . '")
                                                    .siblings(".field-educationdata-series .form-text text-muted")
                                                    .remove();
                                            }
                                        }
                                    ',
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <?php if (!$hideProfileFieldForEducation) : ?>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-4 col-12 control-label">
                            <?= $education_data->getAttributeLabel('profile_ref_id'); ?>
                        </label>

                        <div class="col-sm-8 col-12">
                            <?= Html::hiddenInput(
                                'hidden_profile_ref',
                                $education_data->profile_ref_id,
                                ['id' => 'hidden_profile_ref' . $postfix]
                            ); ?>
                            <?= $form->field($education_data, 'profile_ref_id', ['template' => $template])
                                ->widget(DepDrop::class, [
                                    'language' => $appLanguage,
                                    'class' => 'form-control',
                                    'readonly' => !$canEdit,
                                    'disabled' => !$canEdit,
                                    'type' => DepDrop::TYPE_SELECT2,
                                    'options' => [
                                        'id' => 'educationdata-profile_ref_id' . $postfix,
                                        'placeholder' => Yii::t(
                                            'abiturient/bachelor/education/education-modal',
                                            'Подпись для пустого поля "profile_ref_id"; модального окна обработки образования на странице док. об образ.: `Выберите ...`'
                                        )
                                    ],
                                    'data' => EducationData::getProfileList(),
                                    'select2Options' => [
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'dropdownParent' => "#{$wrapper_id}",
                                        ]
                                    ],
                                    'pluginOptions' => [
                                        'initialize' => false,
                                        'placeholder' => Yii::t(
                                            'abiturient/bachelor/education/education-modal',
                                            'Подпись для пустого поля "profile_ref_id"; модального окна обработки образования на странице док. об образ.: `Выберите ...`'
                                        ),
                                        'url' => Url::to(['/bachelor/edu-profile']),
                                        'depends' => ['educationdata-document_type_id' . $postfix],
                                        'initDepends' => ['educationdata-document_type_id' . $postfix],
                                        'params' => [
                                            'educationdata-education_type_id' . $postfix,
                                            'educationdata-education_level_id' . $postfix,
                                            'educationdata-document_type_id' . $postfix,
                                            'hidden_profile_ref' . $postfix,
                                        ],
                                        'loadingText' => Yii::t(
                                            'abiturient/bachelor/education/education-modal',
                                            'Подпись загружающегося поля "profile_ref_id"; модального окна обработки образования на странице док. об образ.: `Загрузка ...`'
                                        ),
                                    ]
                                ]); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <div class="row">
                    <label class="col-sm-4 col-12 control-label">
                        <?= $education_data->getAttributeLabel('series'); ?>
                    </label>

                    <div class="col-sm-8 col-12">
                        <?php if (isset($first_doc_type) && $first_doc_type->ref_key == Yii::$app->configurationManager->getCode('edu_certificate_doc_type_guid')) {
                            $mask = '[-]*{0,5}';
                        } else {
                            $mask = '[-]*{0,6}';
                        }
                        if (
                            isset($first_doc_type) && ($first_doc_type->ref_key == Yii::$app->configurationManager->getCode('edu_certificate_doc_type_guid')
                                || $first_doc_type->ref_key == Yii::$app->configurationManager->getCode('bak_doc_guid`')
                                || $first_doc_type->ref_key == Yii::$app->configurationManager->getCode('mag_doc_guid`')
                                || $first_doc_type->ref_key == Yii::$app->configurationManager->getCode('spec_doc_guid`'))
                        ) {
                            echo $form->field($education_data, 'series', ['template' => $template])
                                ->widget(MaskedInput::class, [
                                    'mask' => $mask,
                                    'definitions' => [
                                        '*' => [
                                            'validator' => "[0-9A-Za-zА-Яа-я!#$%&'*+/=?^_`{|}~\-]",
                                            'cardinality' => 1,
                                        ]
                                    ],
                                    'options' => [
                                        'id' => 'educationdata-series' . $postfix,
                                        'class' => 'form-control',
                                        'readonly' => !$canEdit,
                                        'disabled' => !$canEdit,
                                        'tabindex' => '3',
                                        'data' => [
                                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries
                                        ],
                                    ],
                                ]);
                        } else {
                            echo $form->field($education_data, 'series', ['template' => $template])
                                ->widget(MaskedInput::class, [
                                    'mask' => '[-]*{0,100}',
                                    'definitions' => ['*' => [
                                        'validator' => "[0-9A-Za-zА-Яа-я!#$%&'*+/=?^_`{|}~\-]",
                                        'cardinality' => 1,
                                    ]],
                                    'options' => [
                                        'id' => 'educationdata-series' . $postfix,
                                        'class' => 'form-control',
                                        'readonly' => !$canEdit,
                                        'disabled' => !$canEdit,
                                        'tabindex' => '3',
                                        'data' => [
                                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentSeries
                                        ],
                                    ],
                                ]);
                        } ?>
                    </div>
                </div>

                <?php $alertMessage = Yii::$app->configurationManager->getText('info_message_in_education_for_series');
                if (!EmptyCheck::isEmpty($alertMessage)) : ?>
                    <div class="row">
                        <div class="col-12">
                            <span class="subtitle-education-series">
                                <?php echo $alertMessage ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group required">
                <div class="row">
                    <label class="col-sm-4 col-12 col-md-5 control-label has-star">
                        <?= $education_data->getAttributeLabel('number'); ?>
                    </label>

                    <div class="col-sm-8 col-12 col-md-7">
                        <?= $form->field($education_data, 'number', ['template' => $template])
                            ->widget(
                                MaskedInput::class,
                                [
                                    'mask' => '*{0,100}',
                                    'options' => [
                                        'id' => 'educationdata-number' . $postfix,
                                        'class' => 'form-control',
                                        'readonly' => !$canEdit,
                                        'disabled' => !$canEdit,
                                        'tabindex' => '4',
                                        'data' => [
                                            'one-s-attribute-name' => RulesProviderByDocumentType::DocumentNumber
                                        ],
                                    ],
                                ]
                            ); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label class="col-sm-4 col-12 col-md-5 control-label has-star">
                        <?= $education_data->getAttributeLabel('date_given'); ?>
                    </label>

                    <div class="col-sm-8 col-12 col-md-7">
                        <?= $form->field($education_data, 'date_given')->widget(
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
                                    'orientation' => 'bottom',
                                    'endDate' => '-1d',
                                ],
                                'options' => [
                                    'readonly' => !$canEdit,
                                    'disabled' => !$canEdit,
                                    'tabindex' => '7',
                                    'autocomplete' => 'off',
                                    'id' => 'educationdata-date_given' . $postfix,
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
            </div>

            <div class="form-group required">
                <div class="row">
                    <label class="col-sm-4 col-md-5 control-label has-star">
                        <?php echo $education_data->getAttributeLabel('contractor_id') ?>
                    </label>
                    <div class="col-sm-8 col-md-7">
                        <?php echo ContractorField::widget([
                            'form' => $form,
                            'model' => $education_data,
                            'attribute' => 'contractor_id',
                            'notFoundAttribute' => 'notFoundContractor',
                            'keynum' => $postfix,
                            'need_subdivision_code' => false,
                            'is_readonly' => !$canEdit,
                            'disabled' => !$canEdit,
                            'labels' => [
                                'contractor_name' => $education_data->getAttributeLabel('contractor_id'),
                            ],
                            'contractor_type_ref_uid' => StoredContractorTypeReferenceType::findByUID(
                                \Yii::$app->configurationManager->getCode('contractor_type_edu_guid')
                            )->reference_uid,
                            'application' => $application,
                            'options' => [
                                'selectInputId' => "education_data-contractor_id_{$postfix}",
                                'contractorTitleInputId' => "education_data-contractor_name_{$postfix}",
                                'contractorSubdivisionCodeInputId' => "education_data-contractor_subdivision_code_{$postfix}",
                                'contractorLocationCodeInputId' => "education_data-contractor_location_code_{$postfix}",
                                'notFoundCheckboxInputId' => "education_data-contractor_not_found_{$postfix}",
                                'approveModalId' => "education_data-contractor_approve_modal_{$postfix}",
                                'data' => [
                                    'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy
                                ]
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="form-group required">
                <div class="row">
                    <label class="col-sm-4 col-12 col-md-5 control-label has-star">
                        <?= $education_data->getAttributeLabel('edu_end_year'); ?>
                    </label>

                    <div class="col-sm-8 col-12 col-md-7">
                        <?= $form->field($education_data, 'edu_end_year', ['template' => $template])->widget(
                            MaskedInput::class,
                            [
                                'mask' => '9999',
                                'options' => [
                                    'id' => 'educationdata-edu_end_year' . $postfix,
                                    'class' => 'form-control',
                                    'readonly' => !$canEdit,
                                    'disabled' => !$canEdit,
                                    'tabindex' => '8',
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <div class="form-group required">
                <div class="row">
                    <label class="col-sm-4 col-12 col-md-5 control-label has-star">
                        <?= $education_data->getAttributeLabel('have_original'); ?>
                    </label>

                    <div class="col-sm-8 col-12 col-md-7">
                        <p class="form-control-static">
                            <?= $education_data->haveOriginal ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php $file_required = $education_data->attachmentCollection->isRequired(); ?>
            <div class="form-group <?= $file_required ? 'required' : '' ?>">
                <?= $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                    'attachmentCollection' => $education_data->attachmentCollection,
                    'isReadonly' => !($canEdit || $addNewFile || $canDeleteFile),
                    'required' => $file_required,
                    'form' => $form,
                    'label' => Yii::t(
                        'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                        'Подпись области прикрепления сканов; в модальном окне ИД на странице ИД: `Скан-копии подтверждающего документа`'
                    ),
                    'addNewFile' => $addNewFile,
                    'canDeleteFile' => $canDeleteFile,
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

    <?php $globalTextForSubmitTooltip = Yii::$app->configurationManager->getText('global_text_for_submit_tooltip'); ?>
    <?php if ($canEdit || $addNewFile || $canDeleteFile) : ?>
        <div class="row">
            <div class="col-12">

                <?= Html::submitButton(
                    Yii::t(
                        'abiturient/bachelor/education/education-modal',
                        'Подпись кнопки для сохранения формы; модального окна обработки образования на странице док. об образ.: `Сохранить`'
                    ) . TooltipWidget::widget([
                        'message' => Yii::$app->configurationManager->getText('education_save_btn_tooltip'),
                        'params' => 'style="margin-left: 4px;"'
                    ]),
                    [
                        'data-tooltip_title' => $globalTextForSubmitTooltip,
                        'class' => "btn btn-primary float-right btn-save-education {$antiClickerClass}",
                    ]
                ); ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>

<?php $this->registerJsVar('globalTextForAjaxTooltip', $globalTextForSubmitTooltip); ?>
<?php $this->registerJsVar('educationSaveUrl', $globalTextForSubmitTooltip);
