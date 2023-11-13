<?php

use common\components\FilesWorker\FilesWorker;
use common\components\imageCropper\Widget;
use common\components\ini\iniGet;
use common\components\PhoneWidget\PhoneWidget;
use common\models\AbiturientAvatar;
use common\models\dictionary\Country;
use common\models\dictionary\Gender;
use common\models\EmptyCheck;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\models\relation_presenters\comparison\widgets\AreaDifferenceWidget;
use common\models\relation_presenters\comparison\widgets\FieldDifferenceWidget;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\PersonalData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\select2\Select2;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\MaskedInput;


















$appLanguage = Yii::$app->language;
$fieldDifferenceWidgetTemplate = "{label}{input}\n{error}";

if (!isset($isIndependentForm)) {
    $isIndependentForm = false;
}

$comparisonEntryArray = ['tomato' => $questionaryComparisonWithActual];

if (isset($questionaryComparisonWithSent)) {
    $comparisonEntryArray['yellow'] = $questionaryComparisonWithSent;
}

?>

<div class="card mb-3">
    <div class="card-header">
        <h4>
            <?= Yii::t(
                'abiturient/questionary_partial/block-basic-data',
                'Заголовок блока "Основные данные" на странице анкеты поступающего: `Основные данные`'
            ); ?>
        </h4>
    </div>

    <div class="card-body">
        <?php if ($isIndependentForm && !$isReadonly && !$disabled) : ?>
            <?= Alert::widget([
                'options' => ['class' => 'saved-success-alert alert-success', 'style' => 'display: none;'],
                'body' => "",
            ]); ?>

            <?= Alert::widget([
                'options' => ['class' => 'error-while-saving alert-danger', 'style' => 'display: none;'],
                'body' => "",
            ]); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group required">
                    <div class="row">
                        <label class="col-12 col-md-4 control-label text-md-right has-star" for="user_email">
                            <?= Yii::t(
                                'abiturient/questionary_partial/block-basic-data',
                                'Подпись поля "email" формы в блоке "Основные данные" на странице анкеты поступающего: `Email`'
                            ) ?>:
                            <?= TooltipWidget::widget([
                                'message' => Yii::$app->configurationManager->getText('email_tooltip'),
                                'params' => 'style="margin-left: 5px;"'
                            ]) ?>
                        </label>

                        <div class="col-12 col-md-8">
                            <?= Html::input(
                                'text',
                                'user_email',
                                ArrayHelper::getValue($questionary, 'user.email'),
                                [
                                    'class' => 'form-control',
                                    'readonly' => $isReadonly,
                                    $disabled => $disabled,
                                    'id' => 'user_email'
                                ]
                            ); ?>
                        </div>
                    </div>
                </div>

                <?php $personalDataTemplate = $fieldDifferenceWidgetTemplate; ?>
                <div class="form-group required">
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.lastname',
                        'field' => $form->field($personalData, 'lastname', ['template' => $personalDataTemplate])
                            ->textInput(['readonly' => !$canChangeFio, $disabled => !$canChangeFio])
                    ]) ?>
                </div>
                <div class="form-group required">
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.firstname',
                        'field' => $form->field($personalData, 'firstname', ['template' => $personalDataTemplate])
                            ->textInput(['readonly' => !$canChangeFio, $disabled => !$canChangeFio])
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.middlename',
                        'field' => $form->field($personalData, 'middlename', ['template' => $personalDataTemplate])
                            ->textInput(['readonly' => !$canChangeFio, $disabled => !$canChangeFio])
                    ]) ?>
                </div>

                <div class="form-group required">
                    <div class="row">
                        <div class="col-12 col-md-4 text-md-right">
                            <label class="control-label">
                                <?php echo $personalData->getAttributeLabel('gender_id'); ?>:
                            </label>
                        </div>
                        <div class="col-12 col-md-8">
                            <?php AreaDifferenceWidget::begin([
                                'comparison_entry' => $comparisonEntryArray,
                                'property_path' => 'personalData.genderName',
                                'wrap_in_flexbox' => true
                            ]) ?>

                            <?php echo $form->field($personalData, 'gender_id', ['template' => $template,])
                                ->radioList(
                                    ArrayHelper::map(
                                        Gender::find()
                                            ->notMarkedToDelete()->active()
                                            ->orFilterWhere([Gender::tableName() . '.id' => $personalData->gender_id])
                                            ->orderBy('code')->all(),
                                        'id',
                                        'description'
                                    ),
                                    [
                                        'item' => function ($index, $label, $name, $checked, $value) use ($disabled) {
                                            $str_checked = '';
                                            if ($checked == 1) {
                                                $str_checked = 'checked';
                                            }

                                            $return = '<label class="modal-radio">';
                                            $return .= "<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$str_checked} {$disabled}>";
                                            $return .= '<em></em>';
                                            $return .= '<span>' . ucwords($label) . '</span>';
                                            $return .= '</label>';

                                            return $return;
                                        },
                                        $disabled => $disabled
                                    ]
                                ); ?>
                            <?php AreaDifferenceWidget::end(); ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-md-4 text-md-right">
                                    <label for="personaldata-birthdate">
                                        <?= $personalData->getAttributeLabel('birthdate'); ?>:
                                    </label>
                                </div>

                                <div class="col-12 col-md-8">
                                    <?php AreaDifferenceWidget::begin([
                                        'comparison_entry' => $comparisonEntryArray,
                                        'property_path' => 'personalData.birthdate',
                                    ]) ?>

                                    <?= $form->field($personalData, 'birthdate')->widget(
                                        DatePickerMaskedWidget::class,
                                        [
                                            'inline' => false,
                                            'language' => $appLanguage,
                                            'template' => "{input}{addon}",
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
                                                $disabled => $disabled,
                                                'readonly' => $isReadonly,
                                                'autocomplete' => 'off'
                                            ],
                                            'maskOptions' => [
                                                'alias' => 'dd.mm.yyyy'
                                            ]
                                        ]
                                    ); ?>
                                    <?php AreaDifferenceWidget::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?php $snilsField = $form->field(
                        $personalData,
                        'snils',
                        ['template' => "{label} {input}\n{error}"]
                    )->widget(
                        MaskedInput::class,
                        [
                            'mask' => '999-999-999 99',
                            'clientOptions' => ['clearMaskOnLostFocus' => false],
                            'options' => [
                                'class' => 'form-control',
                                'readonly' => $isReadonly, $disabled => $disabled
                            ],
                        ]
                    )->label($personalData->getAttributeLabel('snils') . ':' . TooltipWidget::widget([
                        'message' => Yii::$app->configurationManager->getText('snils_tooltip'),
                        'params' => 'style="margin-left: 5px;"'
                    ])); ?>
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.snils',
                        'field' => $snilsField,
                    ]); ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <?php $isRequired = (bool)Yii::$app->configurationManager->getCode('required_abiturient_avatar'); ?>
                    <label class="control-label" for="">
                        <?= Yii::t(
                            'abiturient/questionary_partial/block-basic-data',
                            'Подпись поля "file" формы в блоке "Основные данные" на странице анкеты поступающего: `Фото`'
                        ) ?>:

                        <?php if ($isRequired) : ?>
                            <span style="color: red; margin-left: -5px;">
                                *
                            </span>
                        <?php endif; ?>
                    </label>

                    <?php
                    $abitAvatar->file = ($abitAvatar->isNewRecord ? null : $abitAvatar->getFileDownloadUrl());
                    echo $form
                        ->field($abitAvatar, 'file', ['enableClientValidation' => false])
                        ->widget(Widget::class, [
                            'uploadUrl' => Url::to(['/abiturient/upload-photo', 'questionary_id' => $questionary->id]),
                            'deleteUrl' => Url::to(['/abiturient/delete-photo', 'questionary_id' => $questionary->id,]),
                            'maxSize' => iniGet::getUploadMaxFilesize(false),
                            'extensions' => implode(', ', FilesWorker::getAllowedImageExtensionsToUploadList()),
                            'isReadonly' => $isReadonly,
                        ]);
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group <?php echo QuestionarySettings::getSettingByName('require_birth_place_abiturient') ? 'required' : '' ?>">
                    <?php echo FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.birth_place',
                        'field' => $form->field($personalData, 'birth_place', ['template' => $fieldDifferenceWidgetTemplate])->textInput(['readonly' => $isReadonly, $disabled => $disabled])
                    ]) ?>

                    <?php $alertMessage = Yii::$app->configurationManager->getText('info_message_in_questionary_for_birth_place');
                    if (!EmptyCheck::isEmpty($alertMessage)) : ?>
                        <small class="form-text text-muted text-center">
                            <?php echo $alertMessage ?>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.foreignLanguageName',
                        'field' => $form->field(
                            $personalData,
                            'language_id',
                            ['template' => $fieldDifferenceWidgetTemplate]
                        )->widget(
                            Select2::class,
                            [
                                'language' => $appLanguage,
                                'data' => $personalData->languages,
                                'options' => [
                                    'disabled' => $isReadonly,
                                    'placeholder' => Yii::t(
                                        'abiturient/questionary_partial/block-basic-data',
                                        'Подпись пустого значения выпадающего списка для поля "language_id" формы в блоке "Основные данные" на странице анкеты поступающего: `Выберите язык`'
                                    ),
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]
                        )
                    ]); ?>
                </div>

                <label class="control-label">
                    <?= $personalData->getAttributeLabel('entrant_unique_code'); ?>:
                </label>

                <?php $entrantUniqueCode = '' ?>
                <?php if (empty($personalData->entrant_unique_code)) : ?>
                    <?php $entrantUniqueCode = Yii::t(
                        'abiturient/questionary_partial/block-basic-data',
                        'Информационное сообщение для пустого значения поля "entrant_unique_code" в блоке "Основные данные" на странице анкеты поступающего: `Это поле будет заполнено после первой подачи заявления`'
                    ) ?>
                <?php else : ?>
                    <?php $entrantUniqueCode = Html::encode($personalData->entrant_unique_code); ?>
                <?php endif; ?>

                <?php if ($entrantUniqueCode) : ?>
                    <div class="card mb-3 bg-light">
                        <div class="card-body p-1">
                            <small>
                                <?= $entrantUniqueCode ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($personalData->entrant_unique_code_special_quota)) : ?>
                    <label class="control-label">
                        <?= $personalData->getAttributeLabel('entrant_unique_code_special_quota'); ?>
                    </label>

                    <div class="card mb-3 bg-light">
                        <div class="card-body p-1">
                            <small>
                                <?= Html::encode($personalData->entrant_unique_code_special_quota) ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <div class="form-group <?php echo QuestionarySettings::getSettingByName('require_ctitizenship_abiturient') ? 'required' : ''; ?>">
                    <?php $citizenships = Country::find()
                        ->notMarkedToDelete()
                        ->active()
                        ->orFilterWhere([Country::tableName() . '.id' => $personalData->country_id])
                        ->orderBy(['name' => SORT_ASC])
                        ->all();
                    $citizenship_field = $form->field(
                        $personalData,
                        'country_id',
                        ['template' => $fieldDifferenceWidgetTemplate]
                    )->widget(
                        Select2::class,
                        [
                            'language' => $appLanguage,
                            'data' => ArrayHelper::map($citizenships, 'id', 'name'),
                            'options' => [
                                'disabled' => $isReadonly,
                                'placeholder' => Yii::t(
                                    'abiturient/questionary_partial/block-basic-data',
                                    'Подпись пустого значения выпадающего списка для поля "country_id" формы в блоке "Основные данные" на странице анкеты поступающего: `Не указано`'
                                ),
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]
                    ); ?>
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.citizenshipName',
                        'field' => $citizenship_field,
                    ]); ?>
                </div>

                <div class="form-group required row">
                    <?php [$main_phone_difference, $_] = (new ComparisonHelper($comparisonEntryArray, 'personalData.preparedMainPhone'))
                        ->getRenderedDifference(); ?>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-12 col-md-4 text-md-right">
                                <label class="control-label">
                                    <?= $personalData->getAttributeLabel('main_phone') ?>:
                                </label>

                                <span>
                                    <?= $main_phone_difference ?>
                                </span>
                            </div>

                            <div class="col-12 col-md-8">
                                <?= PhoneWidget::widget([
                                    'form' => $form,
                                    'disabled' => $disabled,
                                    'citizenId' => $citizenId,
                                    'isReadonly' => $isReadonly,
                                    'phoneField' => 'main_phone',
                                    'personalData' => $personalData,
                                    'fieldConfig' => ['template' => $template],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?= FieldDifferenceWidget::widget([
                        'comparison_entry' => $comparisonEntryArray,
                        'property_path' => 'personalData.preparedSecondaryPhone',
                        'field' => $form->field(
                            $personalData,
                            'secondary_phone',
                            ['template' => $template]
                        )->textInput(['readonly' => $isReadonly, $disabled => $disabled]),
                    ]); ?>
                </div>
            </div>
        </div>

        <?php if ($isIndependentForm && !$isReadonly && !$disabled) : ?>
            <div class="row">
                <div class="col-12">
                    <?= Html::submitButton(
                        Yii::t(
                            'abiturient/questionary_partial/block-basic-data',
                            'Подпись кнопки сохранения формы основных данных; в блоке анкеты на странице анкеты поступающего: `Сохранить`'
                        ),
                        ['class' => 'btn btn-primary float-right']
                    ); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>