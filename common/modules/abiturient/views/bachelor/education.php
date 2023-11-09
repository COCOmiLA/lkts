<?php

use common\components\attachmentWidget\AttachmentWidget;
use common\models\AttachmentType;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\services\NextStepService;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;













$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/bachelor/education/all',
    'Заголовок страницы док. об образ.: `Личный кабинет поступающего`'
);

$formId = 'education-data-form';
$isReadonly = false;
$disabled = '';
if (!$canEdit) {
    $disabled = 'disabled';
    $isReadonly = true;
}

$hideProfileFieldForEducation = $application->type->hide_profile_field_for_education;

?>

<?= $this->render('../abiturient/_abiturientheader', [
    'route' => Yii::$app->urlManager->parseRequest(Yii::$app->request)[0],
    'current_application' => $application
]); ?>

<div class="row">
    <?php if (!$isAttachmentsAdded && $canEdit) : ?>
        <div class="col-12">
            <?= $this->render('../abiturient/_fileError', [
                'attachmentErrors' => $attachmentErrors,
            ]); ?>
        </div>
    <?php endif; ?>

    <?php if ($educationTopText = Yii::$app->configurationManager->getText('education_top_text', $application->type ?? null)) : ?>
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <?= $educationTopText; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <?= $this->render(
            'partials/education/_education_pjax',
            [
                'status' => $status,
                'canEdit' => $canEdit,
                'application' => $application,
                'educationDatum' => $education_datum,
                'hasChangedAttributes' => $hasChangedAttributes,
                'applicationComparisonWithActual' => $application_comparison,
                'hideProfileFieldForEducation' => $hideProfileFieldForEducation,
                'allowAddNewEducationAfterApprove' => $allowAddNewEducationAfterApprove,
                'allowAddNewFileToEducationAfterApprove' => $allowAddNewFileToEducationAfterApprove,
                'allowDeleteFileFromEducationAfterApprove' => $allowDeleteFileFromEducationAfterApprove,
            ]
        ); ?>
    </div>

    <div class="col-12">
        <?php if ($regulations || $attachments) : ?>
            <?php $form = ActiveForm::begin([
                'id' => $formId,
                'options' => ['name' => 'EducationForm', 'enctype' => 'multipart/form-data'],
                'fieldConfig' => [
                    'template' => "{input}\n{error}"
                ]
            ]); ?>

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
                            'items' => $attachments,
                            'isReadonly' => $isReadonly,
                            'application' => $application
                        ]
                    ]) ?>
                </div>

                <div class="col-12 ml-2">
                    <?php if (
                        $canEdit ||
                        $application->hasPassedApplicationWithEditableAttachments(AttachmentType::RELATED_ENTITY_EDUCATION)
                    ) {
                        $next_step_service = new NextStepService($application);
                        $message = Yii::t(
                            'abiturient/bachelor/education/all',
                            'Подпись кнопки сохранения формы с образованием; на странице док. об образ.: `Сохранить`'
                        );
                        if ($next_step_service->getUseNextStepForwarding()) {
                            $message = Yii::t(
                                'abiturient/bachelor/education/all',
                                'Подпись кнопки сохранения формы с образованием; на странице док. об образ.: `Сохранить и перейти к следующему шагу`'
                            );
                        }
                        echo Html::submitButton(
                            $message,
                            ['class' => 'btn btn-primary float-right']
                        );

                        echo Html::a(
                            Yii::t(
                                'abiturient/bachelor/education/all',
                                'Подпись кнопки отмены формы с образованием; на странице док. об образ.: `Отмена`'
                            ),
                            Url::toRoute(['bachelor/education', 'id' => $application->id]),
                            ['class' => 'btn btn-outline-secondary float-right mr-2']
                        );
                    }
                    ?>
                </div>
            </div>

            <?php ActiveForm::end() ?>
        <?php else : ?>
            <?php
            $next_step_service = new NextStepService($application);

            if ($next_step_service->getUseNextStepForwarding()) {
                $message = Yii::t(
                    'abiturient/bachelor/education/all',
                    'Подпись кнопки перехода к следующему шагу; на странице док. об образ.: `Перейти к следующему шагу`'
                );

                $next_step = $next_step_service->getNextStep('education');
                if ($next_step !== 'education') {
                    echo Html::a(
                        $message,
                        $next_step_service->getUrlByStep($next_step),
                        ['class' => 'btn btn-primary float-right']
                    );
                }
            }
            ?>
        <?php endif; ?>
    </div>

    <?php if ($educationBottomText = Yii::$app->configurationManager->getText('education_bottom_text', $application->type ?? null)) : ?>
        <div class="alert alert-info" style="margin-top: 15px;" role="alert">
            <?= $educationBottomText; ?>
        </div>
    <?php endif; ?>
</div>