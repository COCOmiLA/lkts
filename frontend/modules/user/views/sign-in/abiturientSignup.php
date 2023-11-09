<?php

use common\components\attachmentWidget\AttachmentWidget;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\models\dictionary\Country;
use common\models\dictionary\DocumentType;
use common\models\Recaptcha;
use common\modules\abiturient\models\PersonalData;
use frontend\modules\user\models\AbiturientSignupForm;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;










$appLanguage = Yii::$app->language;

$this->title = Yii::t(
    'sign-in/abiturient-signup/form',
    'Заголовок страницы авторизации: `Регистрация поступающего`'
);
$this->params['breadcrumbs'][] = $this->title;

$document_type = null;
$documentTypeEntity = CodeSettingsManager::GetEntityByCode('russian_passport_guid');
if (!is_null($documentTypeEntity)) {
    $document_type = $documentTypeEntity->id;
}
$document_type_abroad = DocumentType::findByUID(
    Yii::$app->configurationManager->getCode('foreign_passport_guid')
);
if (!is_null($document_type_abroad)) {
    $document_type_abroad = $document_type_abroad->id;
}

$emailColClass = 'col-12 col-lg-12';
if ($confirmEmail) {
    $emailColClass = 'col-12 col-md-6 col-lg-12';
}

$passwordColClass = 'col-12 col-lg-12';
if ($signupPasswordConfirm = Yii::$app->configurationManager->getSignupPasswordConfirm()) {
    $passwordColClass = 'col-12 col-md-6 col-lg-12';
}

?>

<style>
    .file-error-message ul {
        list-style: none;
    }
</style>

<div class="site-signup style="max-width: 1176px">
    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php if ($errorFrom1C) : ?>
        <?php if ($errorPassport_type) : ?>
            <div class="alert alert-danger" role="alert">
                <?= Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Заголовок алерта информирующего об ошибке валидации паспорта на форме регистрации: `Для паспорта гражданина РФ:`'
                ) ?>

                <ul id="unordered_list_for_alert-danger">
                    <li>
                        <?= Yii::t(
                            'sign-in/abiturient-signup/form',
                            'Пункт алерта информирующего об ошибке валидации паспорта на форме регистрации: `серия паспорта должна состоять из 4 цифр;`'
                        ) ?>
                    </li>

                    <li>
                        <?= Yii::t(
                            'sign-in/abiturient-signup/form',
                            'Пункт алерта информирующего об ошибке валидации паспорта на форме регистрации: `номер паспорта должен состоять из 6 цифр;`'
                        ) ?>
                    </li>
                </ul>
            </div>
        <?php else : ?>
            <div class="alert alert-danger" role="alert">
                <?= Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Алерт информирующий об успешном создании пароля на форме авторизации: `В системе уже есть пользователь с такими данными. Пожалуйста, перейдите по ссылке "{link}"`',
                    ['link' => Html::a(
                        Yii::$app->configurationManager->getText('createacc_link_text'),
                        ['abiturient-access']
                    )]
                ) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($registerTopText = Yii::$app->configurationManager->getText('register_top_text')) : ?>
        <div class="alert alert-info" role="alert">
            <?= $registerTopText; ?>
        </div>
    <?php endif; ?>

    <?php $formId = 'form-signup';
    $form = ActiveForm::begin([
        'id' => $formId,
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="row">
                <div class="<?= $emailColClass ?>">
                    <?= $form->field($model, 'email', ['errorOptions' => ['class' => 'form-text text-muted', 'encode' => false]])
                        ->textInput(['type' => 'email']) ?>
                </div>

                <?php if ($confirmEmail) : ?>
                    <div class="col-12 col-md-6 col-lg-12">
                        <?= $form->field($model, 'confirm_email')
                            ->textInput(['type' => 'email']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="<?= $passwordColClass ?>">
                    <?= $form->field($model, 'password')->passwordInput() ?>
                </div>

                <?php if ($signupPasswordConfirm) : ?>
                    <div class="col-12 col-md-6 col-lg-12">
                        <?= $form->field($model, 'confirm_password')->passwordInput(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 col-lg-12">
                    <?= $form->field($model, 'lastname') ?>
                </div>

                <div class="col-12 col-md-6 col-lg-12">
                    <?= $form->field($model, 'firstname') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 col-lg-12">
                    <?= $form->field($model, 'middlename') ?>
                </div>

                <div class="col-12 col-md-6 col-lg-12">
                    <?= $form->field($model, 'birthday')->widget(
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
                                'endDate' => PersonalData::getMaxBirthdateFormatted(),
                            ],
                            'maskOptions' => ['alias' => 'dd.mm.yyyy']
                        ]
                    ); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'sign-in/abiturient-signup/form',
                            'Заголовок блока с паспортными данными на форме регистрации: `Паспортные данные`'
                        ) ?>
                    </h4>
                </div>

                <?php $keynum = 0; ?>
                <div class="card-body">
                    <?php echo $this->render(
                        '@common/modules/abiturient/views/abiturient/questionary_partial/_passportForm',
                        [
                            'form' => $form,
                            'model' => $model->passportData,
                            'keynum' => $keynum,
                            'passportTypes' => $passportTypes,
                            'show_file_input' => false,
                            'document_type' => $document_type,
                        ]
                    ); ?>

                    <div class="row">
                        <div class="col-12">
                            <?php $citizenship_guid = Yii::$app->configurationManager->getCode('citizenship_guid');
                            $country = Country::findOne(['ref_key' => $citizenship_guid, 'archive' => false]);
                            $citizen_id = !empty($country) ? $country->id : null;
                            $citizenships = Country::find()->notMarkedToDelete()->active()->orderBy('name')->all();
                            $citizenship_codes = ArrayHelper::map($citizenships, 'id', 'datacode');
                            $citizenship_codes[$citizen_id]['Selected'] = true; ?>

                            <?= $form->field($model, 'country_id')->dropDownList(
                                ArrayHelper::map($citizenships, 'id', 'name'),
                                ['class' => 'form-control', 'options' => $citizenship_codes, 'onchange' => "
                                    if (this.selectedOptions[0].innerText != 'Россия') {
                                        $('#{$model->passportData->clientFormName}-document_type_id_{$keynum}').val('{$document_type_abroad}').change();
                                    } else {
                                        $('#{$model->passportData->clientFormName}-document_type_id_{$keynum}').val({$document_type}).change();
                                    }
                                "]
                            ); ?>
                        </div>
                    </div>
                </div>

                <?php if ($footerText = Yii::$app->configurationManager->getText('text_for_passport_form_footer_on_registration_page')) : ?>
                    <div class="card-footer">
                        <em class="h6">
                            <?= $footerText ?>
                        </em>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12">
            <?php $widgetParams = Recaptcha::getWidgetParamsByName('signup'); ?>
            <?php if (!empty($widgetParams)) : ?>
                <?= $form->field(
                    $model,
                    'reCaptcha',
                    ['template' => '{input}']
                )->widget(
                    $widgetParams['class'],
                    $widgetParams['settings']
                ); ?>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <?= AttachmentWidget::widget([
                'formId' => $formId,
                'regulationConfigArray' => [
                    'isReadonly' => false,
                    'items' => $model->regulations,
                    'form' => $form
                ],
                'showAttachments' => false
            ]); ?>
        </div>

        <?php foreach ($model->attachments as $key => $attachment) : ?>
            <?php
            $attachment_model = $attachment->getModelEntity();
            ?>
            <div class="col-12">
                <label class="col-form-label" for="<?= $attachment_model->formName() . $key ?>">
                    <?= $attachment->attachmentType->name . ($attachment->isRequired() ? '<span style="color: red">*</span>' : '') ?>
                </label>
            </div>

            <div class="col-12" style="margin-bottom: 20px">
                <?= $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                    'attachmentCollection' => $attachment,
                    'isReadonly' => false,
                    'form' => $form,
                    'attachment_model' => $attachment_model,
                ]); ?>
            </div>
        <?php endforeach; ?>

        <div class="form-group col-12">
            <?= Html::submitButton(
                Yii::t(
                    'sign-in/abiturient-signup/form',
                    'Подпись кнопки для сохранения формы регистрации: `Зарегистрироваться`'
                ),
                ['class' => 'btn btn-primary float-right', 'name' => 'signup-button']
            ) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <?php if ($registerBottomText = Yii::$app->configurationManager->getText('register_bottom_text')) : ?>
        <div class="alert alert-info" role="alert">
            <?= $registerBottomText; ?>
        </div>
    <?php endif; ?>
</div>