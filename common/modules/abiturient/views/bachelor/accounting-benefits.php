<?php

use common\components\AccountingBenefits\assets\AccountingBenefitsComponentAsset;
use common\components\attachmentWidget\AttachmentWidget;
use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\services\abiturientController\bachelor\accounting_benefits\BenefitsService;
use common\services\abiturientController\bachelor\accounting_benefits\OlympiadsService;
use common\services\abiturientController\bachelor\accounting_benefits\TargetReceptionsService;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

















AccountingBenefitsComponentAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/bachelor/accounting-benefits/all',
    'Заголовок на странице льгот: `Личный кабинет поступающего | Учёт льгот и отличительных признаков`'
);

$hideBenefitsBlock = ArrayHelper::getValue($application, 'type.hide_benefits_block', false);
$hideOlympicBlock = ArrayHelper::getValue($application, 'type.hide_olympic_block', false);
$hideTargetsBlock = ArrayHelper::getValue($application, 'type.hide_targets_block', false);

$error = Yii::$app->session->getFlash('accountingBenefitsError');
$success = Yii::$app->session->getFlash('accountingBenefitsSuccess');

$targets_comparison_helper = null;
$targets_difference = null;
$targets_class = null;
$olympiads_comparison_helper = null;
$olympiads_difference = null;
$olympiads_class = null;
$preferences_comparison_helper = null;
$preferences_difference = null;
$preferences_class = null;
if (isset($application_comparison) && $application_comparison) {
    $targets_comparison_helper = new ComparisonHelper($application_comparison, 'targetReceptions');
    [$targets_difference, $targets_class] = $targets_comparison_helper->getRenderedDifference();

    $olympiads_comparison_helper = new ComparisonHelper($application_comparison, 'olympiads');
    [$olympiads_difference, $olympiads_class] = $olympiads_comparison_helper->getRenderedDifference();

    $preferences_comparison_helper = new ComparisonHelper($application_comparison, 'preferences');
    [$preferences_difference, $preferences_class] = $preferences_comparison_helper->getRenderedDifference();
}
$next_step_service = new \common\modules\abiturient\models\services\NextStepService($application);

?>

<?= $this->render('../abiturient/_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
    'current_application' => $application
]); ?>

<?php if (!empty($error)) : ?>
    <div class="alert alert-danger" role="alert">
        <p>
            <?= $error ?>
        </p>
    </div>
<?php endif; ?>

<?php if (!empty($success)) : ?>
    <div class="alert alert-info" role="alert">
        <p>
            <?= $success ?>
        </p>
    </div>
<?php endif; ?>

<?php if (!$hideBenefitsBlock) : ?>
    <?php if ($benefitsBeforeSpec = Yii::$app->configurationManager->getText('benefits_before_spec', $application->type ?? null)) : ?>
        <div class="alert alert-info" role="alert">
            <?= $benefitsBeforeSpec; ?>
        </div>
    <?php endif; ?>

    <div class="row margin-bottom">
        <div class="col-12">
            <h1>
                <?= Yii::t(
                    'abiturient/bachelor/accounting-benefits/all',
                    'Заголовок блока льготы; на странице льгот: `Льготы`'
                ) ?>
                <?= $preferences_difference ?: '' ?>
            </h1>
        </div>
    </div>

    <div class="category-container accounting-benefits-container">
        <div class="row">
            <div class="col-12">
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

        <?php if ($preferenceAttachments || $preferenceRegulations) : ?>
            <div class="row">
                <div class="col-12">
                    <?php $formId = 'pref-regulation-form';
                    $form = ActiveForm::begin([
                        'options' => [
                            'id' => $formId,
                            'enctype' => 'multipart/form-data'
                        ],
                    ]); ?>

                    <?= AttachmentWidget::widget([
                        'formId' => $formId,
                        'attachmentConfigArray' => [
                            'application' => $application,
                            'isReadonly' => !$resultBenefits['canEdit'],
                            'items' => $preferenceAttachments,
                        ],
                        'regulationConfigArray' => [
                            'items' => $preferenceRegulations,
                            'isReadonly' => !$resultBenefits['canEdit'],
                            'form' => $form,
                        ],
                    ]); ?>

                    <?php
                    $message = Yii::t(
                        'abiturient/bachelor/accounting-benefits/all',
                        'Подпись кнопки отправки формы; льготы на странице льгот: `Сохранить`'
                    );
                    if ($next_step_service->getUseNextStepForwarding()) {
                        $message = Yii::t(
                            'abiturient/bachelor/accounting-benefits/all',
                            'Подпись кнопки отправки формы; льготы на странице льгот: `Сохранить и перейти к следующему шагу`'
                        );
                    }
                    echo Html::submitButton(
                        $message,
                        ['class' => 'btn btn-primary float-right']
                    )
                    ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!$hideTargetsBlock) : ?>
    <?php if ($benefitsBeforeTarget = Yii::$app->configurationManager->getText('benefits_before_target', $application->type ?? null)) : ?>
        <div class="alert alert-info" role="alert">
            <?= $benefitsBeforeTarget; ?>
        </div>
    <?php endif; ?>

    <div class="row margin-bottom">
        <div class="col-12">
            <h1>
                <?= Yii::t(
                    'abiturient/bachelor/accounting-benefits/all',
                    'Заголовок блока целевые договоры; на странице льгот: `Целевые договоры`'
                ) ?>
            </h1>
        </div>
    </div>

    <div class="category-container accounting-benefits-container">
        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
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

        <?php if ($targetReceptionAttachments || $targetReceptionRegulations) : ?>
            <div class="row">
                <div class="col-12">
                    <?php $formId = 'target-regulation-form';
                    $form = ActiveForm::begin([
                        'options' => [
                            'id' => $formId,
                            'enctype' => 'multipart/form-data'
                        ],
                    ]); ?>

                    <?= AttachmentWidget::widget([
                        'formId' => $formId,
                        'attachmentConfigArray' => [
                            'application' => $application,
                            'isReadonly' => !$resultTargets['canEdit'],
                            'items' => $targetReceptionAttachments,
                        ],
                        'regulationConfigArray' => [
                            'isReadonly' => !$resultTargets['canEdit'],
                            'items' => $targetReceptionRegulations,
                            'form' => $form
                        ],
                    ]); ?>

                    <?php
                    $message = Yii::t(
                        'abiturient/bachelor/accounting-benefits/all',
                        'Подпись кнопки отправки формы; блока целевые договоры на странице льгот: `Сохранить`'
                    );
                    if ($next_step_service->getUseNextStepForwarding()) {
                        $message = Yii::t(
                            'abiturient/bachelor/accounting-benefits/all',
                            'Подпись кнопки отправки формы; блока целевые договоры на странице льгот: `Сохранить и перейти к следующему шагу`'
                        );
                    }
                    echo Html::submitButton(
                        $message,
                        ['class' => 'btn btn-primary float-right']
                    )
                    ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!$hideOlympicBlock) : ?>
    <?php if ($benefitsBeforeOlymp = Yii::$app->configurationManager->getText('benefits_before_olymp', $application->type ?? null)) : ?>
        <div class="alert alert-info" role="alert">
            <?= $benefitsBeforeOlymp; ?>
        </div>
    <?php endif; ?>

    <div class="row margin-bottom">
        <div class="col-sm-12">
            <h1>
                <?= Yii::t(
                    'abiturient/bachelor/accounting-benefits/all',
                    'Заголовок блока поступление без вступительных испытаний; на странице льгот: `Поступление без вступительных испытаний`'
                ) ?>
                <?= $olympiads_difference ?: '' ?>
            </h1>
        </div>
    </div>

    <div class="category-container accounting-benefits-container">
        <div class="row ">
            <div class="col-12">
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

        <?php if ($olympAttachments || $olympRegulations) : ?>
            <div class="row mt-3">
                <div class="col-12">
                    <?php $formId = 'olymp-regulation-form';
                    $form = ActiveForm::begin([
                        'options' => [
                            'id' => $formId,
                            'enctype' => 'multipart/form-data'
                        ],
                    ]); ?>

                    <?= AttachmentWidget::widget([
                        'formId' => $formId,
                        'attachmentConfigArray' => [
                            'application' => $application,
                            'isReadonly' => !$resultOlympiads['canEdit'],
                            'items' => $olympAttachments,
                        ],
                        'regulationConfigArray' => [
                            'isReadonly' => !$resultOlympiads['canEdit'],
                            'items' => $olympRegulations,
                            'form' => $form,
                        ],
                    ]); ?>

                    <?php
                    $message = Yii::t(
                        'abiturient/bachelor/accounting-benefits/all',
                        'Подпись кнопки отправки формы; поступление без вступительных испытаний на странице льгот: `Сохранить`'
                    );
                    if ($next_step_service->getUseNextStepForwarding()) {
                        $message = Yii::t(
                            'abiturient/bachelor/accounting-benefits/all',
                            'Подпись кнопки отправки формы; поступление без вступительных испытаний на странице льгот: `Сохранить и перейти к следующему шагу`'
                        );
                    }
                    echo Html::submitButton(
                        $message,
                        ['class' => 'btn btn-primary float-right']
                    )
                    ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (
            !$preferenceAttachments && !$preferenceRegulations &&
            !$olympAttachments && !$olympRegulations &&
            !$targetReceptionAttachments && !$targetReceptionRegulations
        ) {
            if ($next_step_service->getUseNextStepForwarding()) {
                $message = Yii::t(
                    'abiturient/bachelor/accounting-benefits/all',
                    'Подпись кнопки перехода к следующему шагу; на странице льгот.: `Перейти к следующему шагу`'
                );

                $next_step = $next_step_service->getNextStep('accounting-benefits');
                if ($next_step !== 'accounting-benefits') {
                    echo Html::a(
                        $message,
                        $next_step_service->getUrlByStep($next_step),
                        ['class' => 'btn btn-primary float-right']
                    );
                }
            }
        } ?>
    </div>
<?php endif;
