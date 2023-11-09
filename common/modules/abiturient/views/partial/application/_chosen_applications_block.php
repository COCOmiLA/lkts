<?php

use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\services\NextStepService;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
























if (!isset($next_step_service)) {
    $next_step_service = null;
}
if (!isset($renderCommonEducationInput)) {
    $renderCommonEducationInput = true;
}

if (!isset($financialBasisTypeFilter)) {
    $financialBasisTypeFilter = [];
}

$hasVerifiedAgreements = $hasVerifiedAgreements ?? false;

$saveSpecFormIdentification = rand(0, 1000);

$formConfig = [
    'id' => "save-spec-form--{$saveSpecFormIdentification}",
    'options' => ['enctype' => 'multipart/form-data'],
];
if (isset($formAction)) {
    $formAction['action'] = $formAction;
}

?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>
            <?= $cardHeader ?>
            <?= TooltipWidget::widget([
                'message' => Yii::$app->configurationManager->getText('specialities_tooltip', $application->type ?? null)
            ]) ?>
            <?= $applicationsDifference ?: '' ?>
        </h4>

        <div>
            <?php if ($application->canEdit() && $application->canEditSpecialities()) : ?>
                <?= $addNewApplicationModalBtn ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <?php $form = ActiveForm::begin($formConfig); ?>

        <?php if (
            $application->status != BachelorApplication::STATUS_CREATED &&
            $loadFrom_1cInfo = Yii::$app->configurationManager->getText('load_from_1c_info', $application->type ?? null)
        ) : ?>
            <div class="alert alert-info" role="alert">
                <?= $loadFrom_1cInfo; ?>
            </div>
        <?php endif; ?>

        <?php if ($application->agreementRecordsWithoutActiveSpeciality) : ?>
            <div class="alert alert-info" role="alert">
                <ul class="mb-0 ml-0 pl-3">
                    <?php foreach ($application->agreementRecordsWithoutActiveSpeciality as $agreement_record) : ?>
                        <li><?php echo "{$agreement_record->getTypeDescription()} {$agreement_record->speciality_name} {$agreement_record->getFormattedDate()}" ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="panel-container added-specs">
            <?php if (isset($specialities) && $specialities) : ?>
                <?php if ($renderCommonEducationInput && $application->type->rawCampaign->common_education_document) : ?>
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
                <?php endif; ?>

                <?php $hierarchicalSpecialities = $bachelorSpecialityService->makeSpecialitiesListHierarchical(
                    array_filter(
                        $specialities,
                        function ($bachelor_speciality) use ($financialBasisTypeFilter) {
                            $educationSourceRefUid = ArrayHelper::getValue($bachelor_speciality, 'speciality.educationSourceRef.reference_uid');
                            return empty($financialBasisTypeFilter) || in_array($educationSourceRefUid, $financialBasisTypeFilter);
                        }
                    )
                );
                $allowBenefitCategories = !ArrayHelper::getValue($application, 'type.hide_benefits_block', false);
                foreach ($hierarchicalSpecialities as $key => $bachelor_speciality) {
                    $children_bachelor_speciality = [];
                    if (is_array($bachelor_speciality)) {
                        [$bachelor_speciality, $children_bachelor_speciality] = $bachelor_speciality;
                    }
                    
                    $is_child = false;
                    $maxEnrollmentPriority = $specialityPrioritiesService->maxEnrollmentPriority($application, $bachelor_speciality);
                    $maxInnerPriority = $specialityPrioritiesService->maxInnerPriority($application, $bachelor_speciality);
                    echo $this->render(
                        '@common/modules/abiturient/views/partial/application/_chosen_application',
                        compact(
                            'application',
                            'specialities',
                            'key',
                            'bachelor_speciality',
                            'children_bachelor_speciality',
                            'is_child',
                            'form',
                            'display_speciality_name',
                            'display_group_name',
                            'display_code',
                            'isReadonly',
                            'target_receptions',
                            'allowBenefitCategories',
                            'maxEnrollmentPriority',
                            'maxInnerPriority',
                            'hasVerifiedAgreements',
                        )
                    );
                } ?>
            <?php else : ?>
                <div class="alert alert-info" role="alert">
                    <?= Yii::t(
                        'abiturient/bachelor/application/application-block',
                        'Текст сообщения для пустого списка направлений; блока НП на странице НП: `Нет добавленных направлений.`'
                    ) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$isReadonly) : ?>
            <div class="row">
                <div class="col-sm-12">
                    <?php $btnType = 'submitButton';
                    $antiClickerBtn = 'anti-clicker-btn'; ?>
                    <?php if ($enableAutofillSpecialtyOnAUniversalBasis) {
                        $btnType = 'button';
                        $antiClickerBtn = '';
                    }
                    $message = Yii::$app->configurationManager->getText('save_speciality_button_label');
                    if ($next_step_service && $next_step_service->getUseNextStepForwarding()) {
                        $message = Yii::$app->configurationManager->getText('save_speciality_button_label_step_forward');
                    }
                    echo Html::{$btnType}(
                        $message,
                        [
                            'class' => "btn btn-primary save-spec-btn float-right {$antiClickerBtn}",
                            'data-save_spec_form_identification' => $saveSpecFormIdentification,
                        ]
                    )
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>