<?php

use common\components\AddressWidget\AddressWidget;
use common\components\AddressWidget\assets\AddressWidgetAsset;
use common\components\attachmentWidget\AttachmentWidget;
use common\components\PhoneWidget\PhoneWidget;
use common\models\AbiturientAvatar;
use common\models\dictionary\Country;
use common\models\dictionary\Gender;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\User;
use common\modules\abiturient\assets\abiturientQuestionaryAsset\AbiturientQuestionaryAsset;
use common\modules\abiturient\assets\passportDataAsset\PassportDataAsset;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\ApplicationTypeSettings;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\form\ActiveForm;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;












AbiturientQuestionaryAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/questionary/all',
    'Заголовок страницы анкеты поступающего: `Личный кабинет поступающего`'
);

$appLanguage = Yii::$app->language;

$template = "{input}\n{error}";

$formId = 'questionary-form';
$isReadonly = false;
$disabled = '';
if (!$canEdit) {
    $disabled = 'disabled';
    $isReadonly = true;
}

$hasApprovedApps = $questionary->hasApprovedApps();
$allowAddNewPassportAfterApprove = (QuestionarySettings::getSettingByName('allow_add_new_passport_after_approve') && $hasApprovedApps);
$allowEditAddressAfterApprove = (QuestionarySettings::getSettingByName('allow_edit_address_after_approve') && $hasApprovedApps);

$citizenship_guid = Yii::$app->configurationManager->getCode('citizenship_guid');

$country = Country::findOne(['ref_key' => $citizenship_guid, 'archive' => false]);
$citizen_id = !empty($country) ? $country->id : null;

?>

<?= $this->render('_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
]); ?>

<div class="row">
    <div class="col-12">
        <?php if (!$isAttachmentsAdded && $canEdit) : ?>
            <?= $this->render('_fileError', [
                'attachmentErrors' => $attachmentErrors,
            ]); ?>
        <?php endif; ?>

        <?= $this->render('_questionaryStatusMessage', [
            'hasErrors' => false,
            'questionary' => $questionary,
            'currentApplication' => $current_application,
        ]); ?>

        <?php $passErrors = Yii::$app->session->getFlash('passwordError');
        $labels = (new PassportData)->attributeLabels(); ?>
        <?php if ($passErrors) : ?>
            <div class="alert alert-danger">
                <p>
                    <?= Yii::t(
                        'abiturient/questionary/all',
                        'Тело ошибки сохранения паспорта на странице анкеты поступающего: `Ошибка при сохранении паспортных данных:`'
                    ); ?>
                </p>

                <ol>
                    <?php foreach ($passErrors as $key => $errors) : ?>
                        <li><strong><?= $labels[$key] ?></strong>
                            <ul>
                                <?php foreach ($errors as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        <?php endif; ?>

        <?php if ($questionary->status == AbiturientQuestionary::STATUS_SENT && !$hasApplicationType) {
            $alertMessage = Yii::t(
                'abiturient/questionary/all',
                'Сообщение об ошибке сохранении анкеты при отсутствии ПК на странице анкеты поступающего: `Невозможно создать заявление, так как нет приемных кампаний`'
            );
            echo Html::tag('div', $alertMessage, ['class' => 'alert alert-danger']);
        } ?>

        <?php if ($questionaryTopText = Yii::$app->configurationManager->getText('questionary_top_text')) : ?>
            <div class="alert alert-info" role="alert">
                <?= $questionaryTopText; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($edit_block_reasons) && !empty($edit_block_reasons)) : ?>
            <div class="alert alert-warning" role="alert">
                <p>
                    <?= Yii::t(
                        'abiturient/questionary/all',
                        'Сообщение с предупреждением об блокировки анкеты на редактирование на странице анкеты поступающего: `Невозможно редактировать анкету:`'
                    ); ?>
                </p>

                <ul style="margin-left: 2%;">
                    <?php foreach ($edit_block_reasons as $edit_block_reason) : ?>
                        <li>
                            <?= $edit_block_reason ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php $needToSaveQuestionary = Yii::$app->session->getFlash('needToSaveQuestionary');
        if (!empty($needToSaveQuestionary)) : ?>
            <div class="alert alert-danger">
                <?= Yii::t(
                    'abiturient/questionary/all',
                    'Сообщение с ошибкой о не корректно введённых данных анкеты на редактирование на странице анкеты поступающего: `Обнаружены ошибки в указанных данных анкеты. Перед началом работы с заявлением необходимо устранить ошибки ввода данных и сохранить анкету. Для этого проверьте введенные данные и нажмите кнопку "Сохранить" внизу текущей страницы.`'
                ); ?>
            </div>
        <?php endif; ?>
        <?php if ($questionary->linkedBachelorApplication) : ?>
            <div class="alert alert-warning" role="alert">
                <p>
                    <?= Yii::t(
                        'abiturient/questionary/all',
                        'Текст сообщения при просмотре анкеты привязанной к заявлению: `Вы просматриваете анкету поданную в ПК с заявлением`'
                    ); ?>
                    <?php echo $questionary->linkedBachelorApplication->type->name ?>
                    <?= $questionary->linkedBachelorApplication->translateStatus(); ?>
                </p>
                <?php echo Html::a(Yii::t(
                    'abiturient/questionary/all',
                    'Текст ссылки для перехода к редактированию анкеты: `Нажмите сюда для перехода к редактированию анкеты`'
                ), ['/abiturient/questionary']) ?>
            </div>
        <?php elseif ($questionary->draft_status == IDraftable::DRAFT_STATUS_APPROVED) : ?>
            <div class="alert alert-warning" role="alert">
                <p>
                    <?= Yii::t(
                        'abiturient/questionary/all',
                        'Текст сообщения при просмотре принятой анкеты: `Вы просматриваете анкету принятую приёмной комиссией`'
                    ); ?>
                </p>
                <?php echo Html::a(Yii::t(
                    'abiturient/questionary/all',
                    'Текст ссылки для перехода к редактированию анкеты: `Нажмите сюда для перехода к редактированию анкеты`'
                ), ['/abiturient/questionary']) ?>
            </div>
        <?php endif; ?>
        <?php $form = ActiveForm::begin([
            'id' => $formId,
            'options' => ['name' => 'QuestionaryForm', 'enctype' => 'multipart/form-data'],
            'fieldConfig' => [
                'template' => "{input}\n{error}"
            ]
        ]); ?>

        <?php if ($questionary->user->userRef && $questionary->canEditQuestionary()) : ?>
            <div class="row mb-3">
                <div class="col-12">
                    <?php $btnUpdate = Html::a(
                        Yii::t(
                            'abiturient/questionary/update-modal',
                            'Подпись ссылки на обновление данных из ПК на странице анкеты поступающего: `Обновить`'
                        ),
                        ['/abiturient/update',],
                        ['class' => 'btn btn-primary']
                    ) ?>
                    <?php $btnCancellation = Html::button(
                        Yii::t(
                            'abiturient/questionary/update-modal',
                            'Подпись кнопки отмены обновления данных из ПК на странице анкеты поступающего: `Отмена`'
                        ),
                        [
                            'data-dismiss' => 'modal',
                            'class' => 'btn btn-outline-secondary',
                        ]
                    ); ?>
                    <?php Modal::begin([
                        'title' => Html::tag(
                            'h4',
                            Yii::t(
                                'abiturient/questionary/update-modal',
                                'Заголовок модального окна обновления данных из ПК на странице анкеты поступающего: `Подтвердите действие`'
                            )
                        ),
                        'toggleButton' => [
                            'tag' => 'button',
                            'class' => 'btn btn-primary',
                            'label' => Yii::t(
                                'abiturient/questionary/update-modal',
                                'Подпись кнопки открытия модального окна обновления данных из ПК на странице анкеты поступающего: `Обновить анкету из приемной кампании`'
                            )
                        ],
                        'footer' => $btnCancellation . $btnUpdate
                    ]); ?>

                    <p>
                        <?= Yii::t(
                            'abiturient/questionary/update-modal',
                            'Информационное сообщение в модальном окне обновления данных из ПК на странице анкеты поступающего: `Вы уверены, что хотите актуализировать личные данные (Анкета) из приемной кампании? При этом все существующие данные будут перезаписаны информацией содержащейся в Информационной системы вуза`'
                        ) ?>
                    </p>

                    <?php Modal::end(); ?>

                    <?php if (!$canEdit && !$questionary->isNotCreatedDraft()) : ?>
                        <a href="#" class="btn btn-info float-right" data-toggle="modal" data-target="#contactModal">
                            <?= Yii::t(
                                'abiturient/questionary/contact-modal',
                                'Подпись кнопки открытия модального окна изменения контактов на странице анкеты поступающего: `Изменить контактные данные`'
                            ) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <?= $this->render(
                    'questionary_partial/_basic_data_panel',
                    [
                        'form' => $form,
                        'disabled' => $disabled,
                        'template' => $template,
                        'citizenId' => $citizen_id,
                        'abitAvatar' => $abitAvatar,
                        'isReadonly' => $isReadonly,
                        'questionary' => $questionary,
                        'canChangeFio' => $canChangeFio,
                        'personalData' => $personal_data,
                        'questionaryComparisonWithActual' => $questionary_comparison,
                    ]
                ); ?>
            </div>
        </div>

        <?php $passports_comparison = null;
        $passports_difference = null;
        $passports_class = null;
        if (isset($questionary_comparison)) {
            $passports_comparison = (new ComparisonHelper($questionary_comparison, 'passportData'));
            [$passports_difference, $passports_class] = $passports_comparison->getRenderedDifference();
        } ?>

        <div class="row">
            <div class="col-12">
                <div class="card mb-3 custom-kartik-table">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-9 col-12">
                                <h4>
                                    <?= Yii::t(
                                        'abiturient/questionary/block-passport-data',
                                        'Заголовок блока "Паспортные данные" на странице анкеты поступающего: `Паспортные данные`'
                                    ); ?>
                                    <?= $passports_difference ?: '' ?>
                                </h4>
                            </div>

                            <?php if (!$isReadonly || $allowAddNewPassportAfterApprove) : ?>
                                <div class="col-sm-3 col-12">
                                    <?= Html::button(
                                        Yii::t(
                                            'abiturient/questionary/passport-modal',
                                            'Подпись кнопки открытия модального окна для добавления паспорта на странице анкеты поступающего: `Добавить`'
                                        ),
                                        [
                                            'class' => 'btn btn-primary float-right',
                                            'id' => 'btn-add-passport',
                                            'data-toggle' => 'modal',
                                            'data-target' => "#create_modal_passport",
                                            'disabled' => !($canChangePassport || $allowAddNewPassportAfterApprove)
                                        ]
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body" id="passport-grid">
                        <?= $this->render(
                            "questionary_partial/_document_grid",
                            [
                                'passports' => $passports,
                                'isReadonly' => $isReadonly,
                                'canEdit' => $canChangePassport,
                                'comparison_helper' => $passports_comparison,
                                'show_texts_for_abit' => true
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $address_comparison = null;
        $address_rendered_difference = null;
        $address_class = null;
        if (isset($questionary_comparison)) {
            $address_comparison = (new ComparisonHelper($questionary_comparison, 'addressData'));
            [$address_rendered_difference, $address_class] = $address_comparison->getRenderedDifference();
        }

        $disabledForAddressData = $disabled;
        $isReadonlyForAddressData = $isReadonly;
        if ($isReadonly) {
            if (
                $questionary->draft_status == IDraftable::DRAFT_STATUS_CREATED &&
                $allowEditAddressAfterApprove
            ) {
                $isReadonlyForAddressData = false;
                $disabledForAddressData = '';
            }
        }

        ?>

        <div class="row">
            <div class="col-12">
                <div class="card mb-3 living-address">
                    <div class="card-header">
                        <h4>
                            <?= Yii::t(
                                'abiturient/questionary/block-address-data',
                                'Заголовок блока "Адрес" на странице анкеты поступающего: `Адрес постоянной регистрации`'
                            ); ?>
                            <?= TooltipWidget::widget([
                                'message' => Yii::$app->configurationManager->getText('questionary_address_tooltip'),
                                'params' => 'style="margin-left: 5px;"'
                            ]) ?>

                            <?= $address_rendered_difference ?>
                        </h4>
                    </div>

                    <div class="panel-spinner-loader">
                        <img class="spinner-loader light">
                    </div>

                    <div class="card-body hidden-behind-loader">
                        <?= AddressWidget::widget([
                            'template' => $template,
                            'form' => $form,
                            'isReadonly' => $isReadonlyForAddressData,
                            'disabled' => $disabledForAddressData,
                            'addressData' => $address_data,
                            'comparison_helper' => $address_comparison,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        $user = ArrayHelper::getValue($questionary, 'user');
        $can_see_actual_address = true;
        
        if ($user && $user->getApplications()->exists()) {
            
            $tnApplicationTypeSettings = ApplicationTypeSettings::tableName();
            $can_see_actual_address = $user
                ->getApplications()
                ->joinWith(['type.applicationTypeSettings'])
                ->andWhere([
                    "{$tnApplicationTypeSettings}.name" => 'can_see_actual_address',
                    "{$tnApplicationTypeSettings}.value" => 1,
                ])
                ->exists();
        } ?>
        <?php if ($can_see_actual_address) : ?>
            <?php $actual_address_comparison = null;
            $actual_address_rendered_difference = null;
            $actual_address_class = null;
            if (isset($questionary_comparison)) {
                $actual_address_comparison = (new ComparisonHelper($questionary_comparison, 'actualAddressData'));
                [$actual_address_rendered_difference, $actual_address_class] = $actual_address_comparison->getRenderedDifference();
            } ?>

            <div class="row">
                <div class="col-12">
                    <div class="card mb-3 actual-address">
                        <div class="card-header actual-address-title">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <h4>
                                        <?= Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Заголовок блока "Адрес" на странице анкеты поступающего: `Адрес проживания`'
                                        ); ?>
                                        <?php echo TooltipWidget::widget([
                                            'message' => Yii::$app->configurationManager->getText('questionary_actual_address_tooltip'),
                                            'params' => 'style="margin-left: 5px;"'
                                        ]) ?>
                                        <?= $actual_address_rendered_difference ?>
                                    </h4>
                                </div>

                                <div class="col-12 col-md-6 text-right">
                                    <?php
                                    echo Html::button(
                                        Yii::t(
                                            'abiturient/questionary/address-data',
                                            'Подпись формы "Адрес проживания": `Заполнить из адреса по прописке`'
                                        ),
                                        [
                                            'disabled' => $isReadonlyForAddressData,
                                            'class' => 'btn btn-primary fill-address-same',
                                            'data' => [
                                                'from' => '.living-address',
                                                'to' => '.actual-address',
                                            ]
                                        ]
                                    )
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="panel-spinner-loader">
                            <img class="spinner-loader light">
                        </div>

                        <div class="card-body hidden-behind-loader">
                            <div class="actual-address-wrapper">
                                <?= AddressWidget::widget([
                                    'template' => $template,
                                    'form' => $form,
                                    'isReadonly' => $isReadonlyForAddressData,
                                    'disabled' => $disabledForAddressData,
                                    'addressData' => $actual_address_data,
                                    'prefix' => 'actual'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card mb-3 custom-kartik-table">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-9 col-12">
                                <h4>
                                    <?= Yii::t(
                                        'abiturient/questionary/block-parent-data',
                                        'Заголовок блока "Родители" на странице анкеты поступающего: `Данные родителей или законных представителей {message}`',
                                        ['message' => TooltipWidget::widget(['message' => Yii::$app->configurationManager->getText('parents_tooltip')])]
                                    ); ?>
                                </h4>
                            </div>

                            <?php if (!$isReadonly) : ?>
                                <div class="col-sm-3 col-12 text-right">
                                    <?= Html::button(
                                        Yii::t(
                                            'abiturient/questionary/parent-modal',
                                            'Подпись кнопки открытия модального окна для добавления родителя на странице анкеты поступающего: `Добавить`'
                                        ),
                                        [
                                            'class' => 'btn btn-primary',
                                            'id' => 'btn-add-parent',
                                            'data-toggle' => 'modal',
                                            'data-loading-text' => Yii::t(
                                                'abiturient/questionary/parent-modal',
                                                'Подпись загружающегося модального окна родителя на странице анкеты поступающего: `Загрузка`'
                                            ),
                                            'disabled' => !$canEdit
                                        ]
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body" id="div-parent-data">
                        <?= $this->render(
                            'questionary_partial/parentData/_parent_grid',
                            [
                                'parents' => $parents,
                                'canEdit' => $canEdit,
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $other_comparison = null;
        $other_differences = null;
        $other_class = null;
        if (isset($questionary_comparison)) {
            $other_comparison = (new ComparisonHelper($questionary_comparison, 'personalData.humanizedNeedDormitory'));
            [$other_differences, $other_class] = $other_comparison->getRenderedDifference();
        } ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>
                            <?= Yii::t(
                                'abiturient/questionary/block-other',
                                'Заголовок блока "Прочее" на странице анкеты поступающего: `Прочее`'
                            ); ?>
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6 col-12 <?= $other_differences ? $other_class : '' ?>">
                                <div class="row">
                                    <div class="col-8">
                                        <label class="col-sm-6 col-form-label">
                                            <?= $personal_data->getAttributeLabel('need_dormitory') ?>
                                        </label>

                                        <span>
                                            <?= $other_differences ?>
                                        </span>
                                    </div>

                                    <div class="col-4">
                                        <?= $form->field(
                                            $personal_data,
                                            'need_dormitory'
                                        )->checkbox([
                                            'label' => false,
                                            'class' => 'form-checkbox',
                                            'readonly' => $isReadonly,
                                            'disabled' => $isReadonly
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?= AttachmentWidget::widget([
                    'formId' => $formId,
                    'regulationConfigArray' => [
                        'items' => $regulations,
                        'isReadonly' => $isReadonly,
                        'form' => $form
                    ],
                    'attachmentConfigArray' => [
                        'isReadonly' => $isReadonly,
                        'items' => $attachments
                    ]
                ]) ?>
            </div>
        </div>
        <?php if (!$questionary->linkedBachelorApplication && $questionary->draft_status != IDraftable::DRAFT_STATUS_APPROVED) : ?>
            <div class="row">
                <div class="col-12">
                    <?php if (
                        $canEdit ||
                        $canChangeFio ||
                        $allowEditAddressAfterApprove ||
                        $questionary->hasPassedApplicationWithEditableAttachments()
                    ) : ?>
                        <?= Html::submitButton(
                            Yii::t(
                                'abiturient/questionary/all',
                                'Подпись кнопки сохранения изменений в форме анкеты на странице анкеты поступающего: `Сохранить`'
                            ) . TooltipWidget::widget([
                                'message' => Yii::$app->configurationManager->getText('questionary_save_btn_tooltip'),
                                'params' => 'style="margin-left: 4px;"'
                            ]),
                            ['class' => 'btn btn-primary float-right']
                        ); ?>

                        <a class="btn btn-outline-secondary float-right mr-2" href="<?= Url::toRoute(['abiturient/index']); ?>">
                            <?= Yii::t(
                                'abiturient/questionary/all',
                                'Подпись кнопки отмены изменений в форме анкеты на странице анкеты поступающего: `Отмена`'
                            ) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php ActiveForm::end() ?>

        <?php if ($questionaryBottomText = Yii::$app->configurationManager->getText('questionary_bottom_text')) : ?>
            <div class="alert alert-info" style="margin-top: 1rem;" role="alert">
                <?= $questionaryBottomText; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php

echo Html::beginTag('div', ['id' => 'passport-modals']);
echo $this->render('questionary_partial/_passportModals', [
    'isReadonly' => $isReadonly,
    'passports' => $passports,
    'action' => '/abiturient/set-passport',
    'application' => $current_application,
]);
echo Html::endTag('div');


$modalParentHeaderEdit = Yii::t(
    'abiturient/questionary/parent-modal',
    'Заголовок модального окна для редактирования родителя на странице анкеты поступающего: `Редактировать`'
);
if (!$canEdit) {
    $modalParentHeaderEdit = Yii::t(
        'abiturient/questionary/parent-modal',
        'Заголовок модального окна для просмотра родителя на странице анкеты поступающего: `Просмотреть`'
    );
}
$modalParentHeaderCreate = Yii::t(
    'abiturient/questionary/parent-modal',
    'Заголовок модального окна для создания родителя на странице анкеты поступающего: `Создать`'
);
$modalDeleteEntry = Yii::t(
    'abiturient/questionary/parent-modal',
    'Сообщение о подтверждении при удалении родителя на странице анкеты поступающего: `Удалить запись?`'
);
Modal::begin([
    'title' => Html::tag('h4', $modalParentHeaderEdit),
    'size' => 'modal-lg',
    'id' => "edit_modal_parent",
    'options' => [
        'data-parent_form_url' => Url::to([
            '/abiturient/parent-form',
            'current_application_id' => $current_application ? $current_application->id : null
        ]),
        'tabindex' => false,
        'style' => 'overflow-y: auto;' 
    ],
]);
echo "<div id='edit_modal_parent_form'>";
echo "</div>";
Modal::end();

?>

<?php if (!$canEdit) : ?>
    <div class="modal fade bd-example-modal-lg" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        <?= Yii::t(
                            'abiturient/questionary/contact-modal',
                            'Заголовок модального окна изменения контактов на странице анкеты поступающего: `Изменение контактных данных`'
                        ) ?>
                    </h4>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <?= Html::beginForm(Url::toRoute('abiturient/update-contact')); ?>
                <div class="modal-body contact-body">
                    <div class="form-group required">
                        <div class="row">
                            <label class="col-sm-4 col-form-label has-star">
                                <?= Yii::t(
                                    'abiturient/questionary/contact-modal',
                                    'Подпись поля "email" формы модального окна изменения контактов на странице анкеты поступающего: `Email:`'
                                ) ?>
                            </label>

                            <div class="col-sm-8">
                                <?= Html::input("text", "update_email", Yii::$app->user->identity->email, ['class' => 'form-control']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group required">
                        <div class="row">
                            <label class="col-sm-4 col-form-label has-star">
                                <?= Yii::t(
                                    'abiturient/questionary/contact-modal',
                                    'Подпись поля "main_phone" формы модального окна изменения контактов на странице анкеты поступающего: `Основной телефон:`'
                                ) ?>
                            </label>

                            <div class="col-sm-8">
                                <?= PhoneWidget::widget([
                                    'disabled' => false,
                                    'citizenId' => $citizen_id,
                                    'isReadonly' => false,
                                    'phoneField' => 'main_phone',
                                    'personalData' => $personal_data,
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-4 col-form-label">
                                <?= Yii::t(
                                    'abiturient/questionary/contact-modal',
                                    'Подпись поля "secondary_phone" формы модального окна изменения контактов на странице анкеты поступающего: `Дополнительный телефон:`'
                                ) ?>
                            </label>

                            <div class="col-sm-8">
                                <?= Html::input(
                                    'text',
                                    'secondary_phone',
                                    $personal_data->secondary_phone,
                                    ['class' => 'form-control']
                                ); ?>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <?= Yii::t(
                            'abiturient/questionary/contact-modal',
                            'Подпись кнопки отмены в модальном окне изменения контактов на странице анкеты поступающего: `Отмена`'
                        ) ?>
                    </button>

                    <?php $submitBtnLabel = Yii::t(
                        'abiturient/questionary/contact-modal',
                        'Подпись кнопки для сохранения формы модального окна изменения контактов на странице анкеты поступающего: `Обновить`'
                    ) ?>
                    <input type="submit" class="btn btn-primary btn-in-popup" value="<?= $submitBtnLabel ?>" />
                </div>
                <?= Html::endForm(); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$this->registerJsVar('globalTextForAjaxTooltip', Yii::$app->configurationManager->getText('global_text_for_ajax_tooltip'));
$this->registerJsVar('modalDeleteEntry', $modalDeleteEntry);
$this->registerJsVar('modalParentHeaderCreate', $modalParentHeaderCreate);
$this->registerJsVar('modalParentHeaderEdit', $modalParentHeaderEdit);
$this->registerJsVar('formId', $formId);
$this->registerJsVar('russiaCode', Yii::$app->configurationManager->getCode('russia_guid'));
$this->registerJsVar('postalIndexUrl', Yii::$app->urlManager->createUrl('abiturient/postalindex'));
$this->registerJsVar('passportGridUrl', Yii::$app->urlManager->createUrl(['abiturient/passport-grid', 'questionary_id' => $questionary->id]));
$this->registerJsVar('passportModalsUrl', Yii::$app->urlManager->createUrl([
    'abiturient/passport-modals', 
    'questionary_id' => $questionary->id, 
    'app_id' => $current_application->id ?? null
]));
$this->registerJsVar('passportDeletionConfirmation', Yii::t('abiturient/questionary/passport-modal', 'Вы действительно хотите удалить паспорт?'));
AddressWidget::registerJsVarForInitialization();
AddressWidgetAsset::register($this);
PassportDataAsset::register($this);
