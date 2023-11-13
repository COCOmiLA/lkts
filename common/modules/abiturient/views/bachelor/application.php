<?php

use common\components\attachmentWidget\AttachmentWidget;
use common\models\AttachmentType;
use common\models\MaxSpecialityType;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\models\settings\SandboxSetting;
use common\modules\abiturient\assets\applicationAsset\ApplicationAsset;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\services\NextStepService;
use common\services\abiturientController\bachelor\bachelorSpeciality\BachelorSpecialityService;
use common\services\abiturientController\bachelor\bachelorSpeciality\SpecialityPrioritiesService;
use kartik\form\ActiveForm;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;











$this->title = Yii::$app->name . ' | ' . Yii::t(
        'abiturient/bachelor/application/all',
        'Заголовок страницы НП: `Личный кабинет поступающего | Подача заявления`'
    );

ApplicationAsset::register($this);

$isSeparateStatementForFullPaymentBudget = $application->type->rawCampaign->separate_statement_for_full_payment_budget;
$addPaidApplicationModalId = 'new-paid-statement-modal';
$addJointApplicationModalId = 'new-joint-statement-modal';
$addBudgetApplicationModalId = 'new-budget-statement-modal';

$admissionAgreementFile = new AdmissionAgreement();

$display_code = null;
$display_group_name = null;
$display_speciality_name = null;
$enableAutofillSpecialtyOnAUniversalBasis = false;
$sandBoxEnabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);

if ($application) {
    $enableAutofillSpecialtyOnAUniversalBasis =
        $application->type->enable_autofill_specialty_on_a_universal_basis &&
        $application->hasSpecialitiesForAutofill();
}
$this->registerJsVar('enableAutofillSpecialtyOnAUniversalBasis', $enableAutofillSpecialtyOnAUniversalBasis);
$this->registerJsVar('addSpecialitiesUrl', Url::to(['/bachelor/add-specialities', 'id' => $application->id]));
$this->registerJsVar('getAvailableParentSpecialitiesUrl', Url::to(['/bachelor/get-available-parent-specialities']));

try {
    $display_speciality_name = ArrayHelper::getValue($application, 'type.display_speciality_name');
    $display_group_name = ArrayHelper::getValue($application, 'type.display_group_name');
    $display_code = ArrayHelper::getValue($application, 'type.display_code');
} catch (Throwable $exception) {
    Yii::error('Отсутствуют поля настройки отображения направлений подготовки ' . $exception->getMessage());
}

$isReadonly = false;
$disabled = '';
if (!$application->canEdit() || !$application->canEditSpecialities()) {
    $disabled = 'disabled';
    $isReadonly = true;
}
$next_step_service = new NextStepService($application);

echo $this->render('../abiturient/_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
    'current_application' => $application
]);

$this->registerJsVar('maxCount', $max_speciality_count);
$this->registerJsVar('limitType', $limit_type);
$this->registerJsVar('maxSpecialityType', [
    'group' => MaxSpecialityType::TYPE_GROUP,
    'speciality' => MaxSpecialityType::TYPE_SPECIALITY,
    'faculty' => MaxSpecialityType::TYPE_FACULTY,
    'ugs' => MaxSpecialityType::TYPE_UGS,
]);
ApplicationAsset::register($this);

$sandbox_enabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);
$applications_comparison_helper = null;
$applications_difference = null;
$applications_class = null;
if (isset($application_comparison) && $application_comparison) {
    $applications_comparison_helper = new ComparisonHelper($application_comparison, 'bachelorSpecialities');
    [$applications_difference, $applications_class] = $applications_comparison_helper->getRenderedDifference();
}
?>

<?php $consentAddErrors = Yii::$app->session->getFlash('consentAddErrors'); ?>
<?php if ($consentAddErrors) : ?>
    <div class="alert alert-danger" role="alert">
        <p>
            <?= $consentAddErrors; ?>
        </p>
    </div>
<?php endif; ?>

<?php $specialityErrors = Yii::$app->session->getFlash('specialityErrors'); ?>
<?php if ($specialityErrors) :
    foreach ($specialityErrors as $error) : ?>
        <div class="alert alert-danger" role="alert">
            <p><?= $error; ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($add_errors) && $add_errors) :
    foreach ($add_errors as $error) : ?>
        <div class="alert alert-danger" role="alert">
            <p><?= $error; ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php $specTopText = Yii::$app->configurationManager->getText('spec_top_text', $application->type ?? null); ?>
<?php if (!empty($specTopText)) : ?>
    <div class="alert alert-info" role="alert">
        <?= $specTopText ?>
    </div>
<?php endif; ?>

    <div class="alert alert-info">
        <h4>
            <a class="text-decoration-none" href="" data-toggle="collapse" data-target="#collapseInfos"
               aria-expanded="true" aria-controls="collapseInfos">
                <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Показать сроки проведения приёмной кампании`') ?>
            </a>
        </h4>

        <ul id="collapseInfos" class="pl-0 collapse" class="list-group">
            <?php
            $infos = ArrayHelper::getValue($application, 'type.campaign.info', []);
            foreach ($infos as $info) : ?>
                <li class="list-group-item" style="background-color: unset;">
                    <p>
                    <span>
                    <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Уровень подготовки:`') ?>
                    <?php echo ArrayHelper::getValue($info, 'educationLevelRef.reference_name'); ?>
                </span>,

                        <span>
                    <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Финансирование:`') ?>
                    <?php echo $info->financeName; ?>
                </span>,

                        <span>
                    <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Форма обучения:`') ?>
                    <?php echo $info->eduformName; ?>
                </span>,

                        <span>
                    <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Категория приема:`') ?>
                    <?php echo $info->admissionCategory !== null ? $info->admissionCategory->description : 'Подпись в блоке информации об этапах ПК для пустой категории приёма: Не указана' ?>
                </span>,

                        <span>
                    <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Дата окончания приема документов:`') ?>
                    <?php echo date('d.m.Y H:i:s', strtotime($info->date_final)) ?>
                </span>
                    </p>
                    <?php foreach ($info->periodsToSendOriginalEducation as $periodToSendOriginalEducation): ?>
                        <p>
                            <?php echo Yii::t('abiturient/bachelor/application/application-block', 'Подпись в блоке информации об этапах ПК: `Период приема оригиналов документов об образовании:`') ?>
                            <span><?php echo date('d.m.Y H:i:s', strtotime($periodToSendOriginalEducation->start)) ?> - <?php echo date('d.m.Y H:i:s', strtotime($periodToSendOriginalEducation->end)) ?></span>
                        </p>
                    <?php endforeach; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

<?php $configForChosenApplicationsCard = [
    'financialBasisTypeFilter' => null,
    'bachelorSpecialityService' => $bachelorSpecialityService,
    'specialityPrioritiesService' => $specialityPrioritiesService,
    'isReadonly' => $isReadonly,
    'application' => $application,
    'specialities' => $specialities,
    'display_code' => $display_code,
    'next_step_service' => $next_step_service,
    'target_receptions' => $target_receptions,
    'display_group_name' => $display_group_name,
    'applicationsDifference' => $applications_difference,
    'display_speciality_name' => $display_speciality_name,
    'enableAutofillSpecialtyOnAUniversalBasis' => $enableAutofillSpecialtyOnAUniversalBasis,
    'hasVerifiedAgreements' => $application->hasVerifiedAgreements,
]; ?>
<?php if (!$isSeparateStatementForFullPaymentBudget) : ?>
    <div class="row">
        <div class="col-12">
            <?php
            $canPrintByFullPackage = $application->isPrintApplicationByFullPackageAvailable();
            $tooltipText = '';
            if (!$canPrintByFullPackage) {
                $tooltipText = Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Всплывающая подсказка для кнопки "Печать заявления"; блока НП на странице НП: `Для печати необходимо выбрать данные об образовании и добавить, как минимум одно направление`'
                );
            } ?>
            <?= Html::a(
                Html::tag('h5', Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Подпись кнопки печати заявления; блока НП на странице НП: `Печать заявления`'
                )),
                $canPrintByFullPackage ? [
                    'bachelor/print-application-by-full-package',
                    'application_id' => $application->id,
                    'report_type' => 'Application',
                    'application_build_type' => BachelorSpecialityService::BUILD_APPLICATION_TYPE_FULL,
                ] : '#',
                [
                    'target' => $canPrintByFullPackage ? '_blank' : false,
                    'class' => 'btn btn-info mb-3 float-right',
                    'style' => 'white-space: normal;',
                    'disabled' => !$canPrintByFullPackage,
                    'data-toggle' => 'tooltip',
                    'title' => $tooltipText
                ]
            ) ?>
        </div>
    </div>

    <?= $this->render(
        '@common/modules/abiturient/views/partial/application/_chosen_applications_block',
        array_merge(
            $configForChosenApplicationsCard,
            [
                'cardHeader' => Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Заголовок блока НП; на странице НП: `Добавленные направления`'
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
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
        <?= $this->render(
            '@common/modules/abiturient/views/partial/application/_select2_educations_data',
            [
                'form' => $form,
                'model' => $application,
                'attribute' => 'educationsDataTagList',
                'disabled' => $isReadonly || !$application->type->rawCampaign->allow_multiply_education_documents && $application->hasEnlistedBachelorSpeciality(),
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
        <div class="form-group">
            <?php echo Html::submitButton(Yii::t('abiturient/bachelor/application/application-block', 'Общая кнопка для сохранения образований: `Сохранить`'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <?php
            $canPrintByFullPackage = $application->isPrintApplicationByFullPackageAvailable();
            $tooltipText = '';
            if (!$canPrintByFullPackage) {
                $tooltipText = Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Всплывающая подсказка для кнопки "Печать заявления"; блока НП на странице НП: `Для печати необходимо выбрать данные об образовании и добавить, как минимум одно направление`'
                );
            } ?>
            <?= Html::a(
                Html::tag('h5', Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Подпись кнопки печати бюджетного заявления; блока НП на странице НП: `Печать заявления на бюджетную основу`'
                )),
                $canPrintByFullPackage ? [
                    'bachelor/print-application-by-full-package',
                    'application_id' => $application->id,
                    'report_type' => 'Application',
                    'application_build_type' => BachelorSpecialityService::BUILD_APPLICATION_TYPE_BUDGET,
                ] : '#',
                [
                    'target' => $canPrintByFullPackage ? '_blank' : false,
                    'class' => 'btn btn-info mb-3 float-right',
                    'style' => 'white-space: normal;',
                    'disabled' => !$canPrintByFullPackage,
                    'data-toggle' => 'tooltip',
                    'title' => $tooltipText
                ]
            ) ?>
        </div>
    </div>

    <?= $this->render(
        '@common/modules/abiturient/views/partial/application/_chosen_applications_block',
        array_merge(
            $configForChosenApplicationsCard,
            [
                'cardHeader' => Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Заголовок блока НП; на странице НП: `Выбранные направления (бюджетная основа)`'
                ),
                'financialBasisTypeFilter' => $specialityPrioritiesService->getFinancialBasisFilterForBudget(),
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

    <div class="row">
        <div class="col-12">
            <?php
            $canPrintByFullPackage = $application->isPrintApplicationByFullPackageAvailable();
            $tooltipText = '';
            if (!$canPrintByFullPackage) {
                $tooltipText = Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Всплывающая подсказка для кнопки "Печать заявления"; блока НП на странице НП: `Для печати необходимо выбрать данные об образовании и добавить, как минимум одно направление`'
                );
            } ?>
            <?= Html::a(
                Html::tag('h5', Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Подпись кнопки печати платного заявления; блока НП на странице НП: `Печать заявления на платную основу`'
                )),
                $canPrintByFullPackage ? [
                    'bachelor/print-application-by-full-package',
                    'application_id' => $application->id,
                    'report_type' => 'Application',
                    'application_build_type' => BachelorSpecialityService::BUILD_APPLICATION_TYPE_COMMERCIAL,
                ] : '#',
                [
                    'target' => $canPrintByFullPackage ? '_blank' : false,
                    'class' => 'btn btn-info mb-3 float-right',
                    'style' => 'white-space: normal;',
                    'disabled' => !$canPrintByFullPackage,
                    'data-toggle' => 'tooltip',
                    'title' => $tooltipText
                ]
            ) ?>
        </div>
    </div>

    <?= $this->render(
        '@common/modules/abiturient/views/partial/application/_chosen_applications_block',
        array_merge(
            $configForChosenApplicationsCard,
            [
                'cardHeader' => Yii::t(
                    'abiturient/bachelor/application/application-block',
                    'Заголовок блока НП; на странице НП: `Выбранные направления (платная основа)`'
                ),
                'financialBasisTypeFilter' => $specialityPrioritiesService->getFinancialBasisFilterForCommercial(),
                'renderCommonEducationInput' => false,
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

<?php if (isset($specialities) && $specialities && Model::validateMultiple($specialities)) : ?>
    <?php
    $formId = 'application-form-for-attachments';
    $form = ActiveForm::begin([
        'id' => $formId,
        'options' => ['enctype' => 'multipart/form-data'],
        'action' => Url::to(['save-attached-application-files', 'id' => $application->id])
    ]); ?>

    <?= AttachmentWidget::widget([
        'formId' => $formId,
        'regulationConfigArray' => [
            'items' => $regulations,
            'isReadonly' => $isReadonly,
            'form' => $form
        ],
        'attachmentConfigArray' => [
            'isReadonly' => $isReadonly,
            'application' => $application,
            'items' => $attachments
        ]
    ]) ?>

    <?php if (
        !$isReadonly ||
        $application->hasPassedApplicationWithEditableAttachments(AttachmentType::RELATED_ENTITY_APPLICATION)
    ) : ?>
        <div class="row">
            <div class="col-12">
                <?php
                $message = Yii::$app->configurationManager->getText('save_speciality_scans_button_label');
                if ($next_step_service->getUseNextStepForwarding()) {
                    $message = Yii::$app->configurationManager->getText('save_speciality_scans_button_label_step_forward');
                }
                echo Html::submitButton(
                    $message,
                    ['class' => 'btn btn-primary float-right']
                )
                ?>
            </div>
        </div>
    <?php endif; ?>
    <?php ActiveForm::end(); ?>
<?php endif; ?>

<?php $specBottomText = Yii::$app->configurationManager->getText('spec_bottom_text', $application->type ?? null); ?>

<?php if (!empty($specBottomText)) : ?>
    <div class="alert alert-info" role="alert" style="margin-top: 25px;">
        <?= $specBottomText ?>
    </div>
<?php endif; ?>

<?php if (isset($specialities) && $specialities) : ?>
    <?php echo $this->render('@common/modules/abiturient/views/partial/application/_speciality_actions', compact(
        'specialities',
        'application',
    )) ?>

    <?php foreach ($specialities as $bachelor_speciality): ?>
        <?php if ($bachelor_speciality->is_enlisted): ?>
            <?php echo $this->render('@common/modules/abiturient/views/partial/application/_enrollment_rejection_modal', [
                'bachelor_speciality' => $bachelor_speciality,
                'attachments' => [$bachelor_speciality->getEnrollmentRejectionAttachmentCollection()],
                'url' => Url::toRoute([
                    $sandBoxEnabled->value == 1 ? '/abiturient/mark-reject-enrollment' : '/abiturient/reject-enrollment',
                    'bachelor_spec_id' => $bachelor_speciality->id
                ])
            ]); ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($application->canEdit()) : ?>
    <?= $this->render('partials/application/_agreement_modal', ['application' => $application,]) ?>

    <?= $this->render('partials/application/_agreement_decline_modal', ['application' => $application]) ?>

    <?= $this->render('partials/application/_agreement_decline_non_verified_modal', []) ?>

    <?= $this->render('partials/application/_remove_agreement_decline_modal', []) ?>

    <?= $this->render('partials/application/_paid_contract_modal', []) ?>

    <?php $configForAddApplicationModal = [
        'application' => $application,
        'display_code' => $display_code,
        'eduform_array' => $eduform_array,
        'department_array' => $department_array,
        'display_group_name' => $display_group_name,
        'detail_groups_array' => $detail_groups_array,
        'available_specialities' => $available_specialities,
        'display_speciality_name' => $display_speciality_name,
    ]; ?>
    <?php if ($application->canEditSpecialities()) : ?>
        <?php if (!$isSeparateStatementForFullPaymentBudget) : ?>
            <?= $this->render(
                'partials/application/_add_application_modal',
                array_merge(
                    $configForAddApplicationModal,
                    [
                        'cardHeader' => Yii::t(
                            'abiturient/bachelor/application/application-modal',
                            'Заголовок модального окна добавления НП на странице НП: `Добавление направлений подготовки в заявление`'
                        ),
                        'addApplicationModalId' => $addJointApplicationModalId,
                        'finance_array' => $finance_array,
                    ]
                )
            ) ?>
        <?php else : ?>
            <?= $this->render(
                'partials/application/_add_application_modal',
                array_merge(
                    $configForAddApplicationModal,
                    [
                        'cardHeader' => Yii::t(
                            'abiturient/bachelor/application/application-modal',
                            'Заголовок модального окна добавления НП на странице НП: `Добавление направлений подготовки на платной основе в заявление`'
                        ),
                        'addApplicationModalId' => $addPaidApplicationModalId,
                        'financialBasisTypeFilter' => $specialityPrioritiesService->getFinancialBasisFilterForCommercial(),
                        'finance_array' => $specialityPrioritiesService->getFinanceArrayForCommercial($finance_array),
                    ]
                )
            ) ?>
            <?= $this->render(
                'partials/application/_add_application_modal',
                array_merge(
                    $configForAddApplicationModal,
                    [
                        'cardHeader' => Yii::t(
                            'abiturient/bachelor/application/application-modal',
                            'Заголовок модального окна добавления НП на странице НП: `Добавление направлений подготовки на бюджетной основе в заявление`'
                        ),
                        'addApplicationModalId' => $addBudgetApplicationModalId,
                        'financialBasisTypeFilter' => $specialityPrioritiesService->getFinancialBasisFilterForBudget(),
                        'finance_array' => $specialityPrioritiesService->getFinanceArrayForBudget($finance_array),
                    ]
                )
            ) ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($enableAutofillSpecialtyOnAUniversalBasis) : ?>
        <?php echo $this->render('../bachelor/_autofill_specialty_modal', ['bachelorApplication' => $application,]); ?>
    <?php endif; ?>
    <?php echo $this->render('../bachelor/_combined_competitive_group_modal', ['bachelorApplication' => $application,]); ?>
<?php endif;
