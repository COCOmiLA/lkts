<?php

use common\components\AccountingBenefits\assets\AccountingBenefitsComponentAsset;
use common\components\AddressWidget\AddressWidget;
use common\components\AddressWidget\assets\AddressWidgetAsset;
use common\components\attachmentWidget\AttachmentWidget;
use common\components\CommentNavigationLinkerWidget\CommentNavigationLinkerWidget;
use common\models\dictionary\Country;
use common\models\dictionary\SpecialMark;
use common\models\EmptyCheck;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\assets\moderateAsset\ModerateAsset;
use common\modules\abiturient\assets\moderateAsset\ModerateValidationErrorsAsset;
use common\modules\abiturient\assets\passportDataAsset\PassportDataAsset;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\NeedBlockAndUpdateProcessor;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\PersonalData;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\form\ActiveForm;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;















$appLanguage = Yii::$app->language;

$isSeparateStatementForFullPaymentBudget = $application->type->rawCampaign->separate_statement_for_full_payment_budget;
$addPaidApplicationModalId = 'new-paid-statement-modal';
$addJointApplicationModalId = 'new-joint-statement-modal';
$addBudgetApplicationModalId = 'new-budget-statement-modal';
$enableAutofillSpecialtyOnAUniversalBasis = false;
if ($application) {
    $enableAutofillSpecialtyOnAUniversalBasis =
        $application->type->enable_autofill_specialty_on_a_universal_basis &&
        $application->hasSpecialitiesForAutofill();
}

ModerateAsset::register($this);
AccountingBenefitsComponentAsset::register($this);
ModerateValidationErrorsAsset::register($this);
$this->registerJsVar('url_to_validate', Url::to(['/sandbox/validate-application', 'id' => $application->id]));
$this->registerJsVar('app_validate_error_message', Yii::t(
    'sandbox/moderate/all',
    'Текст ошибки о невозможности подачи заявления на панели навигации ЛК: `<strong>Внимание!</strong> Не все обязательные данные внесены.`'
));

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'sandbox/moderate/all',
    'Заголовок страницы проверки анкеты поступающего: `Проверка заявлений`'
);

$template = "{input}\n{error}";
$globalTextForAjaxTooltip = Yii::$app->configurationManager->getText('global_text_for_ajax_tooltip', $application->type ?? null);

$personalData = $questionary->personalData;
$abiturientGenderRef = ArrayHelper::getValue($personalData, 'relGender.ref_key');
$allowBenefitCategories = !ArrayHelper::getValue($application, 'type.hide_benefits_block', false);

$unsaved_leave = Yii::$app->configurationManager->getText('unsaved_leave', $application->type ?? null);

$hideProfileFieldForEducation = $application->type->hide_profile_field_for_education;



$unblock_url = Url::toRoute(['sandbox/unblock', 'id' => $application->id], true);

$citizenship_guid = Yii::$app->configurationManager->getCode('citizenship_guid');
$country = Country::findOne(['ref_key' => $citizenship_guid, 'archive' => false]);
$citizen_id = !empty($country) ? $country->id : null;

$statusEducationSaved = Yii::$app->session->getFlash('educationSaved', false);
?>

<div id="submit-loader" class="overlay">
    <img class="spinner-loader light">
</div>

<?php if (isset($add_errors) && $add_errors) : ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($add_errors as $add_error) : ?>
            <p>
                <strong>
                    <?= $add_error->name; ?>
                </strong>
            </p>

            <ul>
                <?php foreach ($add_error->errors as $error) : ?>
                    <li>
                        <?= $error; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<?php $moderator_allowed_to_edit = $application->type->moderator_allowed_to_edit ?? true; ?>
<?php if ($need_approve_contractor) : ?>
    <?php if ($moderator_allowed_to_edit) : ?>
        <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
    <?php else : ?>
        <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info_blocked'); ?>
    <?php endif; ?>
<?php endif; ?>

<?php $passErrors = Yii::$app->session->getFlash('passwordError');
$labels = (new PassportData())->attributeLabels(); ?>
<?php if ($passErrors) : ?>
    <div class="alert alert-danger">
        <p>
            <?= Yii::t(
                'sandbox/moderate/all',
                'Тело сообщения об ошибке сохранения паспорта; на стр. проверки анкеты поступающего: `Ошибка при сохранении паспортных данных`'
            ) ?>:
        </p>

        <ol>
            <?php foreach ($passErrors as $key => $passportFieldErrors) : ?>
                <li>
                    <strong>
                        <?= $labels[$key] ?>
                    </strong>

                    <ul>
                        <?php foreach ($passportFieldErrors as $passportFieldError) : ?>
                            <li>
                                <?= $passportFieldError ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
<?php endif; ?>

<?php if ($hasChangesIn1CWithNewerDate) : ?>
    <div class="alert alert-danger" role="alert">
        <p>
            <?= NeedBlockAndUpdateProcessor::GetMessageAboutRequiredDeclineForUpdate($application) ?>
        </p>
    </div>
<?php endif; ?>
<?php if ($hasBlockedBy1C) : ?>
    <div class="alert alert-danger" role="alert">
        <p>
            <?= NeedBlockAndUpdateProcessor::GetMessageAboutBlockedBy1C($application) ?>
        </p>
    </div>
<?php endif; ?>

<div class="validation-errors-content">
    <?php if (isset($validation_errors) && $validation_errors) : ?>
        <?= $this->render(
            '_validation_errors',
            ['validationErrors' => $validation_errors]
        ) ?>
    <?php endif; ?>
</div>

<?php $consentAddErrors = Yii::$app->session->getFlash('consentAddErrors'); ?>
<?php if ($consentAddErrors) : ?>
    <div class="alert alert-danger" role="alert">
        <p>
            <?= $consentAddErrors; ?>
        </p>
    </div>
<?php endif; ?>

<?php $checkEgeErrorsAbit = Yii::$app->session->getFlash('checkEgeErrorsAbit');
$checkEgeErrorsAbit = json_decode((string)$checkEgeErrorsAbit); ?>
<?php if ($checkEgeErrorsAbit) : ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($checkEgeErrorsAbit as $add_error) : ?>
            <p>
                <strong>
                    <?= $add_error->name; ?>
                </strong>
            </p>

            <ul style="margin-left: 20px">
                <?php foreach ($add_error->errors as $error) : ?>
                    <li>
                        <?= $error; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $specErrors = Yii::$app->session->getFlash('specialityErrors'); ?>
<?php if ($specErrors) : ?>
    <?php foreach ($specErrors as $specError) : ?>
        <div class="alert alert-danger" role="alert">
            <p>
                <strong>
                    <?= $specError['name']; ?>
                    <?php unset($specError['name']); ?>
                </strong>
            </p>

            <?php foreach ($specError as $key => $inner_error) : ?>
                <p>
                    <?= $inner_error; ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($specialityErrors) && $specialityErrors) : ?>
    <?php foreach ($specialityErrors as $specError) : ?>
        <div class="alert alert-danger" role="alert">
            <p>
                <strong>
                    <?= $specError['name']; ?>
                    <?php unset($specError['name']); ?>
                </strong>
            </p>

            <?php foreach ($specError as $key => $inner_error) : ?>
                <p>
                    <?= $inner_error[0]; ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('bind')) :
    $message = Yii::$app->session->getFlash('bind'); ?>
    <div class="alert alert-success" role="alert">
        <?= $message; ?>
    </div>
<?php endif; ?>

<?php
if ($application->status == ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL) : ?>
    <div class="alert alert-warning" role="alert">
        <?= Yii::t(
            'sandbox/moderate/all',
            'Предупреждение о том, что заявление помечено на удаление; на стр. проверки анкеты поступающего: `Данное заявление находится в статусе <strong>"{status}"</strong>, одобрение этой заявки приведет к отзыву заявления из Информационной системы вуза, а также к полному удалению заявления из портала.`',
            ['status' => Yii::t(
                'abiturient/bachelor/bachelor-application',
                'Название статуса "Помечено на удаление"; формы "Заявления": `Помечено на удаление`'
            )]
        ) ?>
    </div>
<?php endif; ?>


<div class="row">
    <div class="col-6">
        <?php echo Html::a(
            Yii::t(
                'sandbox/moderate/all',
                ' Текст подписи ссылки для просмотра таблицы с архивными заявлениями; на стр. проверки анкеты поступающего: `Перечень ключевых изменений заявления`'
            ),
            Url::to(['/sandbox/view-archive-application', 'user_id' => $application->user_id, 'id' => $application->id]),
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <div class="col-6">
        <a href="<?= Url::to(['sandbox/unblock', 'id' => $application->id]); ?>" class="btn btn-primary float-right">
            <?= Yii::t(
                'sandbox/moderate/all',
                'Подпись кнопки возвращающей к списку с заявлениями; на стр. проверки анкеты поступающего: `Назад к списку заявлений поступающих`'
            ) ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h3>
            <?php switch ($abiturientGenderRef) {
                case PersonalData::getGenderMale():
                    echo Yii::t(
                        'sandbox/moderate/all',
                        'Подпись с ФИО поступающего подавшего заявления для случая если поступающий мужского пола; на стр. проверки анкеты поступающего: `Подал заявление {fio}`',
                        ['fio' => $application->fio]
                    );
                    break;

                case PersonalData::getGenderFemale():
                    echo Yii::t(
                        'sandbox/moderate/all',
                        'Подпись с ФИО поступающего подавшего заявления для случая если поступающий женского пола; на стр. проверки анкеты поступающего: `Подала заявление {fio}`',
                        ['fio' => $application->fio]
                    );
                    break;

                default:
                    echo Yii::t(
                        'sandbox/moderate/all',
                        'Подпись с ФИО поступающего подавшего заявления для случая если не удалось определить пол поступающего; на стр. проверки анкеты поступающего: `Подал(а) заявление {fio}`',
                        ['fio' => $application->fio]
                    );
                    break;
            } ?>
        </h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="change-button-wrapper" style="margin-bottom: 20px; text-align: right;">
            <?php if (
                !EmptyCheck::isEmpty($application->user->userRef)
            ) : ?>
                <?= Html::a(
                    Yii::t(
                        'sandbox/moderate/all',
                        'Подпись кнопки обновления заявления из 1С; на стр. проверки анкеты поступающего: `Актуализировать заявление из ПК`'
                    ),
                    [
                        '/bachelor/update-full-package',
                        'id' => $application->id,
                        'baseUrl' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0]
                    ],
                    [
                        'class' => 'btn btn-primary',
                        'title' => Yii::t(
                            'sandbox/moderate/all',
                            'Текст выплывающей подсказки для кнопки обновления заявления из 1С; на стр. проверки анкеты поступающего: `Обновить анкету и заявление из ПК`'
                        ),
                        'data' => [
                            'toggle' => "tooltip",
                            'placement' => "bottom",
                        ]
                    ]
                ); ?>
            <?php endif; ?>

            <?= $this->render(
                '@abiturient/views/partial/changeHistoryModal/_changeHistoryModalButton',
                ['application' => $application]
            ); ?>
        </div>

        <?= $this->render('@abiturient/views/partial/changeHistoryModal/_changeHistoryModal'); ?>
    </div>
</div>

<?php $appApprovingError = Yii::$app->session->getFlash('appApprovingError', null, true);
if ($returnApplicationStepsInfo = Yii::$app->session->getFlash('returnApplicationStepsInfo', null, true)) {
    $appApprovingError[] = $returnApplicationStepsInfo;
}
$acceptStatusList = [
    BachelorApplication::STATUS_SENT,
    BachelorApplication::STATUS_REJECTED_BY1C,
    BachelorApplication::STATUS_WANTS_TO_RETURN_ALL,
    BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED,
];
if ($appApprovingError && in_array($application->status, $acceptStatusList)) {
    echo $this->render(
        './partial/_application_step_status',
        ['stepsInfo' => $appApprovingError,]
    );
} ?>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/moderate/questionary-block/all',
                        'Заголовок блока анкеты; на стр. проверки анкеты поступающего: `Анкета`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <?php $main_data_form = ActiveForm::begin([
                            'id' => 'questionary-main-data-form',
                            'method' => 'post',
                            'action' => ['/sandbox/save-main-data', 'quest_id' => $questionary->id],
                            'options' => ['class' => 'form-horizontal'],
                            'fieldConfig' => [
                                'template' => $template
                            ]
                        ]); ?>

                        <?= $this->render(
                            '../abiturient/questionary_partial/_basic_data_panel',
                            [
                                'form' => $main_data_form,
                                'disabled' => $canEdit ? '' : 'disabled',
                                'template' => $template,
                                'citizenId' => $citizen_id,
                                'abitAvatar' => $abitAvatar,
                                'isReadonly' => !$canEdit,
                                'questionary' => $questionary,
                                'canChangeFio' => $canEdit,
                                'personalData' => $questionary->personalData,
                                'questionaryComparisonWithSent' => $questionary_comparison_with_sent,
                                'questionaryComparisonWithActual' => $questionary_comparison_with_actual,
                                'isIndependentForm' => true,
                            ]
                        ); ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <?php [
                    'class' => $passports_class,
                    'difference' => $passports_difference,
                    'comparisonHelper' => $passports_comparison_helper,
                ] = ComparisonHelper::buildComparisonAttributes(
                    $questionary_comparison_with_actual,
                    $questionary_comparison_with_sent,
                    'passportData'
                ); ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3 custom-kartik-table">
                            <div class="card-header">
                                <div class="row d-flex align-items-center">
                                    <div class="col-sm-9 col-12">
                                        <h4>
                                            <?= Yii::t(
                                                'abiturient/questionary/block-passport-data',
                                                'Заголовок блока "Паспортные данные" на странице анкеты поступающего: `Паспортные данные`'
                                            ); ?>
                                            <?= $passports_difference ?: '' ?>
                                        </h4>
                                    </div>

                                    <?php if ($canEdit) : ?>
                                        <div class="col-sm-3 col-12 text-right">
                                            <?= Html::button(
                                                Yii::t(
                                                    'abiturient/questionary/passport-modal',
                                                    'Подпись кнопки открытия модального окна для добавления паспорта на странице анкеты поступающего: `Добавить`'
                                                ),
                                                [
                                                    'class' => 'btn btn-primary',
                                                    'id' => 'btn-add-passport',
                                                    'data-toggle' => 'modal',
                                                    'data-target' => "#create_modal_passport"
                                                ]
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body" id="passport-grid">
                                <?php if (ArrayHelper::getValue($pending_contractors, 'passport_data')) : ?>
                                    <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
                                <?php endif; ?>
                                <?= $this->render(
                                    '../abiturient/questionary_partial/_document_grid',
                                    [
                                        'canEdit' => $canEdit,
                                        'passports' => $passports,
                                        'comparison_helper' => $passports_comparison_helper,
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php [
                    'class' => $address_rendered_class,
                    'difference' => $address_rendered_difference,
                    'comparisonHelper' => $address_comparison,
                ] = ComparisonHelper::buildComparisonAttributes(
                    $questionary_comparison_with_actual,
                    $questionary_comparison_with_sent,
                    'addressData'
                ); ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3 living-address">
                            <div class="card-header">
                                <h4>
                                    <?= Yii::t(
                                        'abiturient/questionary/block-address-data',
                                        'Заголовок блока "Адрес" на странице анкеты поступающего: `Адрес постоянной регистрации`'
                                    ); ?>

                                    <?= $address_rendered_difference ?>
                                </h4>
                            </div>

                            <div class="panel-spinner-loader">
                                <img class="spinner-loader light">
                            </div>

                            <div class="card-body hidden-behind-loader">
                                <div class="actual-address-wrapper">
                                    <?php $address_form = ActiveForm::begin([
                                        'id' => 'address-data-form',
                                        'method' => 'post',
                                        'action' => [
                                            '/sandbox/save-address-data',
                                            'type' => 'registration',
                                            'questionary_id' => $questionary->id
                                        ],
                                        'options' => ['class' => 'form-horizontal'],
                                        'fieldConfig' => ['template' => $template]
                                    ]); ?>
                                    <?= Alert::widget([
                                        'options' => ['class' => 'saved-success-alert alert-success', 'style' => 'display: none;'],
                                        'body' => '',
                                    ]); ?>

                                    <?= Alert::widget([
                                        'options' => ['class' => 'error-while-saving alert-danger', 'style' => 'display: none;'],
                                        'body' => '',
                                    ]); ?>

                                    <?= AddressWidget::widget([
                                        'template' => $template,
                                        'form' => $address_form,
                                        'disabled' => $canEdit ? '' : 'disabled',
                                        'isReadonly' => !$canEdit,
                                        'addressData' => $questionary->addressData,
                                        'comparison_helper' => $address_comparison,
                                    ]); ?>

                                    <?php if ($canEdit) : ?>
                                        <div class="row">
                                            <div class="col-12">
                                                <?= Html::submitButton(
                                                    Yii::t(
                                                        'sandbox/moderate/address-block/all',
                                                        'Подпись кнопки сохранения формы адресных данных; в блоке анкеты на стр. проверки анкеты поступающего: `Сохранить`'
                                                    ),
                                                    ['class' => 'btn btn-primary float-right']
                                                ); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($application->type->can_see_actual_address) : ?>
                    <?php [
                        'class' => $actual_address_rendered_class,
                        'difference' => $actual_address_rendered_difference,
                        'comparisonHelper' => $actual_address_comparison,
                    ] = ComparisonHelper::buildComparisonAttributes(
                        $questionary_comparison_with_actual,
                        $questionary_comparison_with_sent,
                        'actualAddressData'
                    ); ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-3 actual-address">
                                <div class="card-header">
                                    <h4>
                                        <?= Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Заголовок блока "Адрес" на странице анкеты поступающего: `Адрес проживания`'
                                        ); ?>
                                        <?= $actual_address_rendered_difference ?>

                                    </h4>
                                </div>

                                <div class="panel-spinner-loader">
                                    <img class="spinner-loader light">
                                </div>

                                <div class="card-body hidden-behind-loader">
                                    <div class="actual-address-wrapper">
                                        <?php $address_form = ActiveForm::begin([
                                            'id' => 'actual-address-data-form',
                                            'method' => 'post',
                                            'action' => [
                                                '/sandbox/save-address-data',
                                                'type' => 'actual',
                                                'questionary_id' => $questionary->id
                                            ],
                                            'options' => ['class' => 'form-horizontal'],
                                            'fieldConfig' => ['template' => $template]
                                        ]); ?>
                                        <?= Alert::widget([
                                            'options' => ['class' => 'saved-success-alert alert-success', 'style' => 'display: none;'],
                                            'body' => '',
                                        ]); ?>

                                        <?= Alert::widget([
                                            'options' => ['class' => 'error-while-saving alert-danger', 'style' => 'display: none;'],
                                            'body' => '',
                                        ]); ?>

                                        <?= AddressWidget::widget([
                                            'template' => $template,
                                            'form' => $address_form,
                                            'disabled' => $canEdit ? '' : 'disabled',
                                            'isReadonly' => !$canEdit,
                                            'addressData' => $questionary->getOrCreateActualAddressData(),
                                            'prefix' => 'actual',
                                            'comparison_helper' => $actual_address_comparison,
                                        ]); ?>

                                        <?php if ($canEdit) : ?>
                                            <div class="row">
                                                <div class="col-12">
                                                    <?= Html::submitButton(
                                                        Yii::t(
                                                            'sandbox/moderate/address-block/all',
                                                            'Подпись кнопки сохранения формы адресных данных; в блоке анкеты на стр. проверки анкеты поступающего: `Сохранить`'
                                                        ),
                                                        ['class' => 'btn btn-primary float-right']
                                                    ); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php ActiveForm::end(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php [
                    'class' => $parents_class,
                    'difference' => $parents_difference,
                    'comparisonHelper' => $parents_comparison_helper,
                ] = ComparisonHelper::buildComparisonAttributes(
                    $questionary_comparison_with_actual,
                    $questionary_comparison_with_sent,
                    'parentData'
                ); ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3 custom-kartik-table">
                            <div class="card-header">
                                <div class="row d-flex align-items-center">
                                    <div class="col-sm-9 col-12">
                                        <h4>
                                            <?= Yii::t(
                                                'abiturient/questionary/block-parent-data',
                                                'Заголовок блока "Родители" на странице анкеты поступающего: `Данные родителей или законных представителей {message}`',
                                                ['message' => TooltipWidget::widget(['message' => Yii::$app->configurationManager->getText('parents_tooltip')])]
                                            ); ?>

                                            <?= $parents_difference ?: '' ?>
                                        </h4>
                                    </div>

                                    <?php if ($canEdit) : ?>
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
                                                    'disabled' => false
                                                ]
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body" id="div-parent-data">
                                <?php if (ArrayHelper::getValue($pending_contractors, 'parent_passport_data')) : ?>
                                    <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
                                <?php endif; ?>
                                <?= $this->render(
                                    '../abiturient/questionary_partial/parentData/_parent_grid',
                                    [
                                        'parents' => $parents,
                                        'canEdit' => $canEdit,
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php [
                    'class' => $other_class,
                    'difference' => $other_differences,
                ] = ComparisonHelper::buildComparisonAttributes(
                    $questionary_comparison_with_actual,
                    $questionary_comparison_with_sent,
                    'personalData.humanizedNeedDormitory'
                ); ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <?= Yii::t(
                                        'abiturient/questionary/block-other',
                                        'Заголовок блока "Прочее" на странице анкеты поступающего: `Прочее`'
                                    ); ?>
                                </h4>
                            </div>

                            <div class="card-body">
                                <?php $other_form = ActiveForm::begin([
                                    'id' => 'others-data-form',
                                    'method' => 'post',
                                    'action' => ['/sandbox/save-main-data', 'quest_id' => $questionary->id],
                                    'options' => ['class' => 'form-horizontal'],
                                    'fieldConfig' => ['template' => $template]
                                ]); ?>

                                <?= Alert::widget([
                                    'options' => ['class' => 'saved-success-alert alert-success', 'style' => 'display: none;'],
                                    'body' => '',
                                ]); ?>

                                <?= Alert::widget([
                                    'options' => ['class' => 'error-while-saving alert-danger', 'style' => 'display: none;'],
                                    'body' => '',
                                ]); ?>

                                <div class="row">
                                    <div class="form-group col-6 <?= $other_differences ? $other_class : '' ?>">
                                        <div class="row">
                                            <div class="col-8">
                                                <label class="control-label">
                                                    <?= $personalData->getAttributeLabel('need_dormitory'); ?>:

                                                    <span>
                                                        <?= $other_differences ?>
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="col-4">
                                                <?= $other_form->field($personalData, 'need_dormitory')
                                                    ->checkbox([
                                                        'label' => false,
                                                        'class' => 'form-checkbox',
                                                        'readonly' => !$canEdit,
                                                        'disabled' => !$canEdit,
                                                    ]); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($canEdit) : ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <?= Html::submitButton(
                                                Yii::t(
                                                    'sandbox/moderate/other-block/all',
                                                    'Подпись кнопки сохранения формы прочих данных; в блоке анкеты на стр. проверки анкеты поступающего: `Сохранить`'
                                                ),
                                                ['class' => 'btn btn-primary float-right']
                                            ); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php ActiveForm::end(); ?>
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
        <?= $this->render(
            '../bachelor/partials/education/_education_pjax',
            [
                'status' => $statusEducationSaved,
                'canEdit' => $canEdit,
                'application' => $application,
                'educationDatum' => $application->educations,
                'applicationComparisonWithActual' => $application_comparison_with_actual,
                'applicationComparisonWithSent' => $application_comparison_with_sent,
                'hideProfileFieldForEducation' => $application->type->hide_profile_field_for_education,
                'allowAddNewEducationAfterApprove' => $canEdit,
                'allowAddNewFileToEducationAfterApprove' => $canEdit,
                'allowDeleteFileFromEducationAfterApprove' => $canEdit,
                'has_pending_contractor' => ArrayHelper::getValue($pending_contractors, 'education', false)
            ]
        ); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/moderate/accounting-benefits-block/all',
                        'Заголовок блока льгот; на стр. проверки анкеты поступающего: `Преимущественные права, льготы и целевые направления`'
                    ) ?>
                </h4>
            </div>

            <?php $targets_comparison_helper = null;
            $targets_difference = null;
            $targets_class = null;
            $olympiads_comparison_helper = null;
            $olympiads_difference = null;
            $olympiads_class = null;
            $preferences_comparison_helper = null;
            $preferences_difference = null;
            $preferences_class = null;


            if ($application_comparison_with_sent || $application_comparison_with_actual) {
                $targets_comparison_helper = new ComparisonHelper([
                    'yellow' => $application_comparison_with_sent,
                    'tomato' => $application_comparison_with_actual,
                ], 'targetReceptions');
                [$targets_difference, $targets_class] = $targets_comparison_helper->getRenderedDifference();

                if ($allowBenefitCategories) {
                    $olympiads_comparison_helper = new ComparisonHelper([
                        'yellow' => $application_comparison_with_sent,
                        'tomato' => $application_comparison_with_actual,
                    ], 'olympiads');
                    [$olympiads_difference, $olympiads_class] = $olympiads_comparison_helper->getRenderedDifference();

                    $preferences_comparison_helper = new ComparisonHelper([
                        'yellow' => $application_comparison_with_sent,
                        'tomato' => $application_comparison_with_actual,
                    ], 'preferences');
                    [$preferences_difference, $preferences_class] = $preferences_comparison_helper->getRenderedDifference();
                }
            }
            $targetsCellCallback = $targets_comparison_helper ? $targets_comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc();
            $olympiadsCellCallback = $olympiads_comparison_helper ? $olympiads_comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc();
            $preferencesCellCallback = $preferences_comparison_helper ? $preferences_comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc(); ?>

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <?php if ($allowBenefitCategories) : ?>
                            <div class="row">
                                <div class="col-md-3 col-12">
                                    <h4>
                                        <?= Yii::t(
                                            'sandbox/moderate/accounting-benefits-block/privileges',
                                            'Заголовок таблицы льгот; в блоке льгот на стр. проверки анкеты поступающего: `Льготы`'
                                        ) ?>
                                        <?= $preferences_difference ?: '' ?>
                                    </h4>
                                </div>

                                <div class="col-md-9 col-12">
                                    <hr>
                                </div>

                                <div class="col-12">
                                    <div class="accounting-benefits-container">
                                        <?php if (ArrayHelper::getValue($pending_contractors, 'special_rights')) : ?>
                                            <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
                                        <?php endif; ?>
                                        <div class="card mb-3">
                                            <?= $this->render(
                                                '@common/components/AccountingBenefits/_benefits',
                                                ArrayHelper::merge($resultBenefits, [
                                                    'preferences_comparison_helper' => $preferences_comparison_helper,
                                                    'application' => $application,
                                                    'benefitsService' => $benefitsService,
                                                ])
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-3 col-12">
                                <h4>
                                    <?= Yii::t(
                                        'sandbox/moderate/accounting-benefits-block/target-areas',
                                        'Заголовок таблицы ЦП; в блоке льгот на стр. проверки анкеты поступающего: `Целевые направления`'
                                    ) ?>
                                </h4>
                            </div>

                            <div class="col-md-9 col-12">
                                <hr>
                            </div>

                            <div class="col-12">
                                <?php $cardClass = 'card';
                                if ($allowBenefitCategories) {
                                    $cardClass = 'card mb-3';
                                } ?>
                                <div class="accounting-benefits-container">
                                    <?php if (ArrayHelper::getValue($pending_contractors, 'targets')) : ?>
                                        <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
                                    <?php endif; ?>
                                    <div class="<?= $cardClass ?>">
                                        <?= $this->render(
                                            '@common/components/TargetReception/_target_reception',
                                            ArrayHelper::merge($resultTargets, [
                                                'targets_comparison_helper' => $targets_comparison_helper,
                                                'application' => $application,
                                                'targetReceptionsService' => $targetReceptionsService,
                                            ])
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($allowBenefitCategories) : ?>
                            <div class="row">
                                <div class="col-md-3 col-12">
                                    <h4>
                                        <?= Yii::t(
                                            'sandbox/moderate/accounting-benefits-block/assistant-right',
                                            'Заголовок таблицы преимущественного права; в блоке льгот на стр. проверки анкеты поступающего: `Преимущественные права`'
                                        ) ?>
                                        <?= $olympiads_difference ?: '' ?>
                                    </h4>
                                </div>

                                <div class="col-md-9 col-12">
                                    <hr>
                                </div>

                                <div class="col-12">
                                    <div class="accounting-benefits-container">
                                        <?php if (ArrayHelper::getValue($pending_contractors, 'olympiads')) : ?>
                                            <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
                                        <?php endif; ?>
                                        <div class="card">
                                            <?= $this->render(
                                                '@common/components/AccountingBenefits/_olympiad',
                                                ArrayHelper::merge($resultOlympiads, [
                                                    'olympiads_comparison_helper' => $olympiads_comparison_helper,
                                                    'application' => $application,
                                                    'olympiadsService' => $olympiadsService,
                                                ])
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php [
    'class' => $applications_comparison_class,
    'difference' => $applications_comparison_difference,
    'comparisonHelper' => $applications_comparison_helper,
] = ComparisonHelper::buildComparisonAttributes(
    $application_comparison_with_actual,
    $application_comparison_with_sent,
    'bachelorSpecialities'
); ?>
<div class="row">
    <div class="col-12">
        <?php $display_speciality_name = null;
        $display_group_name = null;
        $display_code = null;
        $formAction = ['/sandbox/save-application', 'id' => $application->id];
        try {
            $display_code = ArrayHelper::getValue($application, 'type.display_code');
            $display_group_name = ArrayHelper::getValue($application, 'type.display_group_name');
            $display_speciality_name = ArrayHelper::getValue($application, 'type.display_speciality_name');
        } catch (Exception $exception) {
            Yii::error('Отсутствуют поля настройки отображения направлений подготовки ' . $exception->getMessage());
        }
        $configForChosenApplicationsCard = [
            'financialBasisTypeFilter' => null,
            'bachelorSpecialityService' => $bachelorSpecialityService,
            'specialityPrioritiesService' => $specialityPrioritiesService,
            'formAction' => $formAction,
            'isReadonly' => !$canEdit,
            'application' => $application,
            'specialities' => $specialities,
            'display_code' => $display_code,
            'target_receptions' => $target_receptions,
            'display_group_name' => $display_group_name,
            'applicationsDifference' => $applications_comparison_difference,
            'display_speciality_name' => $display_speciality_name,
            'enableAutofillSpecialtyOnAUniversalBasis' => $enableAutofillSpecialtyOnAUniversalBasis,
            'hasVerifiedAgreements' => $application->hasVerifiedAgreements,
        ]; ?>
        <?php if (!$isSeparateStatementForFullPaymentBudget) : ?>
            <?= $this->render(
                '@common/modules/abiturient/views/partial/application/_chosen_applications_block',
                array_merge(
                    $configForChosenApplicationsCard,
                    [
                        'cardHeader' => Yii::t(
                            'sandbox/moderate/application-block/all',
                            'Заголовок блока НП на стр. проверки анкеты поступающего: `Направления подготовки`'
                        ),
                        'addNewApplicationModalBtn' => Html::button(
                            Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Подпись кнопки открытия модального окна добавления НП; на странице НП: `Добавить`'
                            ),
                            [
                                'data-toggle' => 'modal',
                                'class' => 'btn btn-primary',
                                'data-target' => "#{$addJointApplicationModalId}",
                            ]
                        )
                    ]
                )
            ); ?>
        <?php else : ?>
            <?php if ($application->type->rawCampaign->common_education_document) : ?>
                <?php $form = ActiveForm::begin([
                    'action' => $formAction,
                    'options' => ['enctype' => 'multipart/form-data'],
                ]); ?>
                <?= $this->render(
                    '@common/modules/abiturient/views/partial/application/_select2_educations_data',
                    [
                        'form' => $form,
                        'model' => $application,
                        'attribute' => 'educationsDataTagList',
                        'disabled' => !$canEdit || !$application->type->rawCampaign->allow_multiply_education_documents && $application->hasEnlistedBachelorSpeciality(),
                        'multiple' => !!$application->type->rawCampaign->allow_multiply_education_documents,
                        'data' => array_reduce(
                            $application->educations,
                            function ($carry, $edu) {
                                
                                $carry[$edu->id] = $edu->getDescriptionString();
                                return $carry;
                            },
                            []
                        ),
                    ]
                ); ?>
                <?php if ($canEdit) : ?>
                    <div class="form-group">
                        <?php echo Html::submitButton(Yii::t('abiturient/bachelor/application/application-block', 'Общая кнопка для сохранения образований: `Сохранить`'), ['class' => 'btn btn-primary']) ?>
                    </div>
                <?php endif; ?>
                <?php ActiveForm::end(); ?>
            <?php endif; ?>

            <?= $this->render(
                '@common/modules/abiturient/views/partial/application/_chosen_applications_block',
                array_merge(
                    $configForChosenApplicationsCard,
                    [
                        'cardHeader' => Yii::t(
                            'sandbox/moderate/application-block/all',
                            'Заголовок блока НП на стр. проверки анкеты поступающего: `Выбранные направления (бюджетная основа)`'
                        ),
                        'financialBasisTypeFilter' => [
                            BachelorSpeciality::getBudgetBasis(),
                            BachelorSpeciality::getTargetReceptionBasis(),
                        ],
                        'renderCommonEducationInput' => false,
                        'addNewApplicationModalBtn' => Html::button(
                            Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Подпись кнопки открытия модального окна добавления НП; на странице НП: `Добавить для бюджетной основы`'
                            ),
                            [
                                'data-toggle' => 'modal',
                                'class' => 'btn btn-primary',
                                'data-target' => "#{$addBudgetApplicationModalId}",
                            ]
                        )
                    ]
                )
            ); ?>

            <?= $this->render(
                '@common/modules/abiturient/views/partial/application/_chosen_applications_block',
                array_merge(
                    $configForChosenApplicationsCard,
                    [
                        'cardHeader' => Yii::t(
                            'sandbox/moderate/application-block/all',
                            'Заголовок блока НП на стр. проверки анкеты поступающего: `Выбранные направления (платная основа)`'
                        ),
                        'renderCommonEducationInput' => false,
                        'financialBasisTypeFilter' => [BachelorSpeciality::getCommercialBasis()],
                        'addNewApplicationModalBtn' => Html::button(
                            Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Подпись кнопки открытия модального окна добавления НП; на странице НП: `Добавить для платной основы`'
                            ),
                            [
                                'data-toggle' => 'modal',
                                'class' => 'btn btn-primary',
                                'data-target' => "#{$addPaidApplicationModalId}",
                            ]
                        )
                    ]
                )
            ); ?>
        <?php endif; ?>

        <?php if ($canEdit) : ?>
            <?php $configForAddApplicationModal = [
                'application' => $application,
                'display_code' => $display_code,
                'eduform_array' => $eduform_array,
                'finance_array' => $finance_array,
                'department_array' => $department_array,
                'display_group_name' => $display_group_name,
                'detail_groups_array' => $detail_groups_array,
                'available_specialities' => $available_specialities,
                'display_speciality_name' => $display_speciality_name,
            ]; ?>
            <?php if (!$isSeparateStatementForFullPaymentBudget) : ?>
                <?= $this->render(
                    '../bachelor/partials/application/_add_application_modal',
                    array_merge(
                        $configForAddApplicationModal,
                        [
                            'cardHeader' => Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Заголовок модального окна добавления НП на странице НП: `Добавление направлений подготовки в заявление`'
                            ),
                            'addApplicationModalId' => $addJointApplicationModalId,
                        ]
                    )
                ) ?>
            <?php else : ?>
                <?= $this->render(
                    '../bachelor/partials/application/_add_application_modal',
                    array_merge(
                        $configForAddApplicationModal,
                        [
                            'cardHeader' => Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Заголовок модального окна добавления НП на странице НП: `Добавление направлений подготовки на платной основе в заявление`'
                            ),
                            'addApplicationModalId' => $addPaidApplicationModalId,
                            'financialBasisTypeFilter' => [BachelorSpeciality::getCommercialBasis()],
                        ]
                    )
                ) ?>
                <?= $this->render(
                    '../bachelor/partials/application/_add_application_modal',
                    array_merge(
                        $configForAddApplicationModal,
                        [
                            'cardHeader' => Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Заголовок модального окна добавления НП на странице НП: `Добавление направлений подготовки на бюджетной основе в заявление`'
                            ),
                            'addApplicationModalId' => $addBudgetApplicationModalId,
                            'financialBasisTypeFilter' => [
                                BachelorSpeciality::getBudgetBasis(),
                                BachelorSpeciality::getTargetReceptionBasis(),
                            ],
                        ]
                    )
                ) ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!$application->egeDisabled) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'abiturient/bachelor/ege/all',
                            'Заголовок таблицы с наборами ВИ; на стр. ВИ: `Набор вступительных испытаний`'
                        ) ?>
                    </h4>
                </div>

                <div class="card-body">
                    <div class="tab-content bachelor-tab">
                        <?php if ($canEdit) : ?>
                            <?php $disciplineSetForm = ActiveForm::begin([
                                'id' => 'discipline-set-form',
                                'method' => 'POST',
                                'action' => ['/bachelor/define-discipline-set', 'id' => $application->id],
                                'options' => ['class' => 'form-horizontal'],

                            ]); ?>

                            <?= Alert::widget([
                                'options' => ['class' => 'saved-success-alert alert-success', 'style' => 'display: none;'],
                                'body' => '',
                            ]); ?>

                            <?= Alert::widget([
                                'options' => ['class' => 'error-while-saving alert-danger', 'style' => 'display: none;'],
                                'body' => '',
                            ]); ?>

                            <?php $egeResult = new EgeResult();
                            $egeResult->application_id = $application->id; ?>
                            <?= $this->render(
                                '../bachelor/_competitiveGroupEntranceTests',
                                [
                                    'form' => $disciplineSetForm,
                                    'id' => $application->id,
                                    'results' => $egeResults,
                                    'newEgeResult' => $egeResult,
                                    'disable' => !$canEdit,
                                    'competitiveGroupEntranceTest' => $competitiveGroupEntranceTest,
                                ]
                            ) ?>

                            <?php if ($canEdit) : ?>
                                <div class="card-actions" style="text-align: right">
                                    <button id="save-discipline-sets" class="btn btn-primary" type="submit">
                                        <?= Yii::t(
                                            'sandbox/moderate/discipline-set-block/all',
                                            'Подпись кнопки сохранения формы наборов ВИ; в блоке анкеты на стр. проверки заявления поступающего: `Сохранить`'
                                        ) ?>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php ActiveForm::end() ?>
                        <?php else : ?>
                            <?= $this->render(
                                '_staticCompetitiveGroupEntranceTests',
                                [
                                    'id' => $application->id,
                                    'results' => $application->egeResults,
                                    'competitiveGroupEntranceTest' => $competitiveGroupEntranceTest,
                                ]
                            ) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php [
        'class' => $exams_comparison_class,
        'difference' => $exams_comparison_difference,
        'comparisonHelper' => $exams_comparison_helper,
    ] = ComparisonHelper::buildComparisonAttributes(
        $application_comparison_with_actual,
        $application_comparison_with_sent,
        'egeResults'
    ); ?>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>
                            <?= Yii::t(
                                'abiturient/bachelor/ege/all',
                                'Заголовок таблицы с результатами ВИ; на стр. ВИ: `Результаты вступительных испытаний`'
                            ) ?>

                            <?= $exams_comparison_difference ?: '' ?>
                        </h4>
                    </div>
                </div>

                <div class="card-body">
                    <?php if ($application->haveEgeConflicts()) : ?>
                        <div style="margin: 20px 0;">
                            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                                <p>
                                    <?= Yii::t(
                                        'sandbox/moderate/ege-block/all',
                                        'Текст информационного сообщения об наличие архивных записей; в блоке анкеты на стр. проверки анкеты поступающего: `Обнаружены архивные или повторяющиеся дисциплины. Перезаполните список вступительных испытаний.`'
                                    ) ?>
                                </p>

                                <a href="<?= Url::toRoute(['bachelor/reload-ege', 'id' => $application->id]) ?>" class="btn btn-primary">
                                    <?= Yii::t(
                                        'abiturient/bachelor/ege/all',
                                        'Подпись кнопки перезаполнения ВИ; на стр. ВИ: `Перезаполнить список вступительных испытаний`'
                                    ) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php $successEgeReload = Yii::$app->session->getFlash('successEgeReload'); ?>
                    <?php if ($successEgeReload === true) : ?>
                        <div style="margin: 20px 0;">
                            <div class="alert alert-success">
                                <p>
                                    <?= Yii::t(
                                        'abiturient/bachelor/ege/all',
                                        'Информационный алерт о том что ВИ были успешно перезаполнены; на стр. ВИ: `Вступительные испытания успешно перезаполнены`'
                                    ) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($successEgeReload === false) : ?>
                        <div style="margin: 20px 0;">
                            <div class="alert alert-danger">
                                <p>
                                    <?= Yii::t(
                                        'sandbox/moderate/ege-block/all',
                                        'Текст информационного сообщения об наличие повторяющихся данных записей; в блоке анкеты на стр. проверки анкеты поступающего: `Невозможно разрешить конфликт повторяющихся дисциплин. Необходимо отклонить заявление для уточнения поступающим списка вступительных испытаний.`'
                                    ) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php $ege_form = ActiveForm::begin([
                        'id' => 'ege-form',
                        'method' => 'post',
                        'action' => ['/sandbox/save-exam-results', 'id' => $application->id],
                        'options' => ['class' => 'form-horizontal'],

                    ]); ?>

                    <?= Alert::widget([
                        'options' => ['class' => 'saved-success-alert alert-success', 'style' => 'display: none;'],
                        'body' => '',
                    ]); ?>

                    <?= Alert::widget([
                        'options' => ['class' => 'error-while-saving alert-danger', 'style' => 'display: none;'],
                        'body' => '',
                    ]); ?>

                    <div class="tab-content bachelor-tab">
                        <?= $this->render(
                            '../bachelor/_egeResult',
                            [
                                'form' => $ege_form,
                                'disable' => !$canEdit,
                                'application' => $application,
                                'egeResults' => $egeResults,
                                'useCurrentYearAsDefault' => false
                            ]
                        ) ?>
                    </div>

                    <?php if ($canEdit) : ?>
                        <div class="card-actions" style="text-align: right">
                            <button id="save-exams" class="btn btn-primary" type="submit">
                                <?= Yii::t(
                                    'sandbox/moderate/ege-block/all',
                                    'Подпись кнопки сохранения формы ВИ; в блоке анкеты на стр. проверки анкеты поступающего: `Сохранить`'
                                ) ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php ActiveForm::end() ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /*INDIVIDUAL ACHIEVEMENTS CSS*/
    .ind .table-responsive {
        margin: 0;
    }

    @media screen and (max-width: 720px) {
        .ind .kv-grid-wrapper {
            height: 300px;
        }

        .ind .category-container {
            margin-left: 0;
        }
    }

    .ind .panel-body {
        padding: 0;
    }

    @media screen and (min-width: 720px) {
        .ind .kv-grid-wrapper {
            height: auto;
        }

        .ind .category-container {
            margin-left: 24px;
        }
    }

    .ind .kv-grid-wrapper table {
        border: none;
    }

    .margin-bottom {
        margin-bottom: 20px;
    }
</style>

<div class="row">
    <div class="col-12">
        <?php if (ArrayHelper::getValue($pending_contractors, 'ia')) : ?>
            <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
        <?php endif; ?>
        <?= $this->render(
            '../abiturient/ialist_partial/_ialist_panel',
            [
                'canEdit' => $canEdit,
                'application' => $application,
                'individualAchievementsDataProvider' => $individualAchievements,
                'applicationComparisonWithSent' => $application_comparison_with_sent,
                'applicationComparisonWithActual' => $application_comparison_with_actual,
            ]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?= $this->render(
            'partial/_copies_documents_panel',
            [
                'application' => $application,
                'questionary' => $questionary,
                'allow_remove' => $canEdit,
            ]
        ) ?>
    </div>
</div>

<?php if ($application->history) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'sandbox/moderate/stories-of-changes/all',
                            'Заголовок блока истории изменений на стр. проверки анкеты поступающего: `История изменений`'
                        ) ?>
                    </h4>
                </div>

                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($application->history as $history) : ?>
                            <li>
                                <?= $history->typeName; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <?= AttachmentWidget::widget([
            'disableFileSizeValidation' => true,
            'regulationConfigArray' => [
                'isReadonly' => true,
                'items' => $regulations
            ],
            'showAttachments' => false
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    <?= Yii::t(
                        'sandbox/moderate/comments-entering/all',
                        'Заголовок блока комментариев поступающего на стр. проверки анкеты поступающего: `Комментарии поступающего`'
                    ) ?>
                </h4>
            </div>

            <div class="card-body">
                <?php if ($application->commentsComing) : ?>
                    <table class="table valign-middle">
                        <tr>
                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/comments-entering/all',
                                    'Заголовок колонки "Автор"; в блоке комментариев поступающего на стр. проверки анкеты поступающего: `Автор`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/comments-entering/all',
                                    'Заголовок колонки "Комментарий"; в блоке комментариев поступающего на стр. проверки анкеты поступающего: `Комментарий`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/comments-entering/all',
                                    'Заголовок колонки "Время"; в блоке комментариев поступающего на стр. проверки анкеты поступающего: `Время`'
                                ) ?>
                            </th>
                        </tr>
                        <?php foreach ($application->commentsComing as $commentsComingItem) : ?>
                            <tr>
                                <td>
                                    <?= $commentsComingItem->author->userProfile->getFullName(); ?>
                                </td>

                                <td class="application-comment">
                                    <?= Html::encode($commentsComingItem->comment); ?>
                                </td>

                                <td>
                                    <?= Yii::$app->formatter->asDatetime($commentsComingItem->created_at); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else : ?>
                    <div class="alert alert-info" role="alert">
                        <?= Yii::t(
                            'sandbox/moderate/comments-entering/all',
                            'Текст для пустой таблицы; в блоке комментариев поступающего на стр. проверки анкеты поступающего: `Нет комментариев`'
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($application->moderateHistory) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'sandbox/moderate/history-of-comments/all',
                            'Заголовок блока истории комментариев на стр. проверки анкеты поступающего: `История комментариев`'
                        ) ?>
                    </h4>
                </div>

                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/history-of-comments/all',
                                    'Заголовок колонки "Дата"; в блоке истории комментариев на стр. проверки анкеты поступающего: `Дата`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/history-of-comments/all',
                                    'Заголовок колонки "Комментарий"; в блоке истории комментариев на стр. проверки анкеты поступающего: `Комментарий`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/history-of-comments/all',
                                    'Заголовок колонки "Статус"; в блоке истории комментариев на стр. проверки анкеты поступающего: `Статус`'
                                ) ?>
                            </th>

                            <th>
                                <?= Yii::t(
                                    'sandbox/moderate/history-of-comments/all',
                                    'Заголовок колонки "Модератор"; в блоке истории комментариев на стр. проверки анкеты поступающего: `Модератор`'
                                ) ?>
                            </th>
                        </tr>

                        <?php foreach ($application->moderateHistory as $moderateHistory) : ?>
                            <tr>
                                <td>
                                    <?= date('d.m.Y H:i', $moderateHistory->moderated_at); ?>
                                </td>

                                <td class="application-comment">
                                    <?= $moderateHistory->comment; ?>
                                </td>

                                <td>
                                    <?= $moderateHistory->statusName; ?>
                                </td>

                                <td>
                                    <?= $moderateHistory->userName; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <?php $form = ActiveForm::begin([
            'id' => 'questionary-form',
            'options' => ['class' => 'form-horizontal'],
        ]); ?>

        <?php
        $approveActionUrl = Url::to();
        if ($application->status == BachelorApplication::STATUS_WANTS_TO_RETURN_ALL) {
            $approveActionUrl = $form->action = Url::toRoute(['/abiturient/return-application', 'id' => $application->id]);
        }
        if ($application->status == BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED) {
            $bach_spec_to_reject = $application->getBachelorSpecialityToRejectEnrollment($specialities ?? []);
            $approveActionUrl = $form->action = Url::toRoute([
                '/abiturient/reject-enrollment', 
                'bachelor_spec_id' => $bach_spec_to_reject->id ?? null
            ]);
        }
        if ($hasChangesIn1CWithNewerDate) {
            
            $application->moderator_comment = Yii::t(
                'abiturient/bachelor/bachelor-application',
                'Текст комментария модератора, при автоматическом обновлении устаревшего заявления; формы "Заявления": `Ваше заявление устарело и было актуализировано из приемной кампании`'
            );
        }
        ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>
                            <?= Yii::t(
                                'sandbox/moderate/ege-block/all',
                                'Заголовок блока комментария на стр. проверки анкеты поступающего: `Комментарий`'
                            ) ?>
                        </h4>

                        <?= CommentNavigationLinkerWidget::widget([
                            'applicationType' => $application->type,
                            'textAriaId' => 'bachelorapplication-moderator_comment',
                        ]) ?>
                    </div>

                    <div class="card-body">
                        <?= $form->field($application, 'moderator_comment', ['template' => $template])
                            ->textarea(['rows' => 5]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (!$hasChangesIn1CWithNewerDate) : ?>
                    <?php $approveBtnLabel = Yii::t(
                        'sandbox/moderate/all',
                        'Подпись кнопки одобрения заявления; на стр. проверки анкеты поступающего: `Одобрить`'
                    );
                    $approveSubmitBtn = Html::submitButton(
                        '<i class="fa fa-check" aria-hidden="true"></i>' . $approveBtnLabel,
                        ['class' => 'btn btn-success float-right moderate-actor', 'data-click' => '0', 'id' => 'apply-button']
                    );
                    $approveModalCancelBtn = Html::button(Yii::t(
                        'sandbox/moderate/all',
                        'Подпись кнопки отмены одобрения заявления; на стр. проверки анкеты поступающего: `Отмена`'
                    ), [
                        'data-dismiss' => 'modal',
                        'class' => 'btn btn-outline-secondary',
                    ]); ?>

                    <?php if (Yii::$app->configurationManager->getPortalManagerSetting('need_approvement_and_declination_confirm')) : ?>
                        <?php Modal::begin([
                            'id' => 'confirm-approve-modal',
                            'title' => Html::tag('h4', Yii::t(
                                'sandbox/moderate/all',
                                'Заголовок окна подтверждения действия; в модальном окне при подтверждении отзыва заявления; на стр. проверки анкеты поступающего: `Подтвердите действие`'
                            )),
                            'toggleButton' => [
                                'tag' => 'button',
                                'class' => 'btn btn-success float-right',
                                'label' => '<i class="fa fa-check" aria-hidden="true"></i>' . $approveBtnLabel
                            ],
                            'footer' => $approveModalCancelBtn . $approveSubmitBtn
                        ]);

                        $confirmMessage = Yii::t(
                            'sandbox/moderate/all',
                            'Текст сообщения; в модальном окне при подтверждении одобрения заявления; на стр. проверки анкеты поступающего: `Вы уверены, что хотите одобрить заявление?`'
                        );
                        if ($application->status == ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL) {
                            $confirmMessage = Yii::t(
                                'sandbox/moderate/all',
                                'Текст сообщения; в модальном окне при подтверждении отзыва заявления; на стр. проверки анкеты поступающего: `Вы уверены, что хотите отозвать заявление из Информационной системы вуза?`'
                            );
                        }
                        echo $confirmMessage;

                        Modal::end(); ?>
                    <?php else : ?>
                        <?php echo $approveSubmitBtn ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (
                    Yii::$app->configurationManager->getCode('allow_dump_full_package_to_file')
                ) : ?>
                    <?= Html::a(
                        Yii::t(
                            'sandbox/moderate/all',
                            'Подпись кнопки сохранения заявления в файл; на стр. проверки анкеты поступающего: `Сохранить заявление в файл`'
                        ),
                        ['/sandbox/full-package-xml', 'id' => $application->id],
                        ['class' => 'btn btn-info float-right mr-2', 'target' => '_blank']
                    ); ?>
                <?php endif; ?>

                <?php $declineBtnLabel = Yii::t(
                    'sandbox/moderate/all',
                    'Подпись кнопки отклонения заявления; на стр. проверки анкеты поступающего: `Отклонить`'
                );
                $declineLink = Html::a(
                    '<i class="fa fa-remove" aria-hidden="true"></i>' . $declineBtnLabel,
                    Url::toRoute(['sandbox/decline', 'id' => $application->id]),
                    [
                        'id' => 'reject-button',
                        'data-click' => '0',
                        'class' => 'btn btn-danger float-right moderate-actor mr-2',
                    ]
                );
                $declineModalCancelBtn = Html::button(Yii::t(
                    'sandbox/moderate/all',
                    'Подпись кнопки отмены одобрения заявления; на стр. проверки анкеты поступающего: `Отмена`'
                ), [
                    'data-dismiss' => 'modal',
                    'class' => 'btn btn-outline-secondary',
                ]); ?>
                <?php if (Yii::$app->configurationManager->getPortalManagerSetting('need_approvement_and_declination_confirm')) : ?>
                    <?php Modal::begin([
                        'id' => 'confirm-decline-modal',
                        'title' => Html::tag('h4', Yii::t(
                            'sandbox/moderate/all',
                            'Заголовок окна подтверждения действия; в модальном окне при подтверждении отзыва заявления; на стр. проверки анкеты поступающего: `Подтвердите действие`'
                        )),
                        'toggleButton' => [
                            'tag' => 'a',
                            'class' => 'btn btn-danger float-right mr-2',
                            'label' => '<i class="fa fa-remove" aria-hidden="true"></i>' . $declineBtnLabel
                        ],
                        'footer' => $declineModalCancelBtn . $declineLink
                    ]);

                    echo Yii::t(
                        'sandbox/moderate/all',
                        'Текст сообщения; в модальном окне при подтверждении отклонения заявления; на стр. проверки анкеты поступающего: `Вы уверены, что хотите отклонить заявление?`'
                    );

                    Modal::end(); ?>
                <?php else : ?>
                    <?php echo $declineLink; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>

<?php

$ajaxErrorText = Yii::t(
    'sandbox/moderate/all',
    'Текст ошибки обработки форм сохранение которых не приводит к перезагрузке страниц (ajax) проверки анкеты поступающего: `Не удалось сохранить данные, обратитесь к администратору`'
);
$createModalHeaderlabel = Yii::t(
    'sandbox/moderate/all',
    'Подпись заголовка модального окна добавлении страниц проверки анкеты поступающего: `Создать`'
);
$editModalHeaderlabel = Yii::t(
    'sandbox/moderate/all',
    'Подпись заголовка модального окна редактирования страниц проверки анкеты поступающего: `Редактировать`'
);

?>

<div id="passport-modals">
    <?= $this->render('../abiturient/questionary_partial/_passportModals', [
        'passports' => $passports,
        'application' => $application,
        'isReadonly' => !$canEdit,
        'action' => '/sandbox/set-passport',
        'modalPassportHeaderCreate' => $createModalHeaderlabel,
        'modalPassportHeaderEdit' => $editModalHeaderlabel,
    ]); ?>
</div>

<?php


Modal::begin([
    'title' => Html::tag('h4', $editModalHeaderlabel),
    'size' => 'modal-lg',
    'id' => "edit_modal_parent",
    'options' => [
        'tabindex' => false,
    ],
]);
echo "<div id='edit_modal_parent_form'>";
echo "</div>";
Modal::end();

if (isset($specialities) && $specialities) {
    echo $this->render('@common/modules/abiturient/views/partial/application/_speciality_actions', [
        'specialities' => $specialities,
        'application' => $application,
        'isReadonly' => false,
        'hideProfileFieldForEducation' => $hideProfileFieldForEducation,
    ]);
}

$this->registerJsVar('applicationId', $application->id);
$this->registerJsVar('editModalHeaderlabel', $editModalHeaderlabel);
$this->registerJsVar('globalTextForAjaxTooltip', $globalTextForAjaxTooltip);
$this->registerJsVar('ajaxErrorText', $ajaxErrorText);
$this->registerJsVar('unsavedLeave', $unsaved_leave);
$this->registerJsVar('russiaCode', Yii::$app->configurationManager->getCode('russia_guid'));
$this->registerJsVar('postalIndexUrl', Yii::$app->urlManager->createUrl('abiturient/postalindex'));
$this->registerJsVar('unblockUrl', $unblock_url);
$this->registerJsVar('passportGridUrl', Yii::$app->urlManager->createUrl(['abiturient/passport-grid', 'questionary_id' => $questionary->id]));
$this->registerJsVar('passportModalsUrl', Yii::$app->urlManager->createUrl([
    'abiturient/passport-modals',
    'questionary_id' => $questionary->id,
    'app_id' => $application->id,
]));
$this->registerJsVar('passportDeletionConfirmation', Yii::t('sandbox/moderate/all', 'Вы действительно хотите удалить паспорт?'));
$this->registerJsVar('approveActionUrl', $approveActionUrl);

AddressWidget::registerJsVarForInitialization();
AddressWidgetAsset::register($this);
PassportDataAsset::register($this);


Modal::begin([
    'title' => '<h4>Внимание</h4>',
    'id' => 'create_modal_is_unblocked',
]);
echo '<div>Заявление разблокировано администратором</div>';
Modal::end();
