<?php

use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\views\bachelor\assets\EducationPjaxAsset;
use yii\web\View;
use yii\widgets\Pjax;
















EducationPjaxAsset::register($this);
$this->registerJsVar('isFirstEducation', empty($educationDatum));

if (!isset($applicationComparisonWithSent)) {
    $applicationComparisonWithSent = null;
}

$has_pending_contractor = $has_pending_contractor ?? false;
?>

<?php Pjax::begin([
    'id' => 'education-data-container',
    'timeout' => 3000
]); ?>
<?php if (
    $status &&
    $educationSaveSuccess = Yii::$app->configurationManager->getText('education_save_success', $application->type ?? null)
) : ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success" role="alert">
                <?= $educationSaveSuccess; ?>
                <br>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (
    isset($hasChangedAttributes) &&
    !$hasChangedAttributes &&
    $educationNoDataSavedText = Yii::$app->configurationManager->getText('no_data_saved_text', $application->type ?? null)
) : ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <?= $educationNoDataSavedText; ?>
                <br>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($educationErrors = Yii::$app->session->getFlash('educationErrors')) : ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <?= $educationErrors ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->render(
    '_education_panel',
    [
        'canEdit' => $canEdit,
        'application' => $application,
        'educationDatum' => $educationDatum,
        'applicationComparisonWithActual' => $applicationComparisonWithActual,
        'hideProfileFieldForEducation' => $hideProfileFieldForEducation,
        'allowAddNewEducationAfterApprove' => $allowAddNewEducationAfterApprove,
        'allowAddNewFileToEducationAfterApprove' => $allowAddNewFileToEducationAfterApprove,
        'allowDeleteFileFromEducationAfterApprove' => $allowDeleteFileFromEducationAfterApprove,
        'has_pending_contractor' => $has_pending_contractor
    ]
); ?>

<!-- Модальные окна -->
<?php $title = Yii::t(
    'abiturient/bachelor/education/education-modal',
    'Заголовок модального окна обработки образования на странице док. об образ.: `Просмотреть`'
);
$canUpdate = $canEdit || $allowAddNewFileToEducationAfterApprove || $allowDeleteFileFromEducationAfterApprove;
if ($canUpdate) {
    $title = Yii::t(
        'abiturient/bachelor/education/education-modal',
        'Заголовок модального окна обработки образования на странице док. об образ.: `Редактировать`'
    );
} ?>
<?php foreach ($educationDatum as $educationData) : ?>
    <?= $this->render(
        '_education_modal',
        [
            'postfix' => $educationData->id,
            'title' => $title,
            'canEdit' => $canEdit,
            'modal_id' => 'edit_education_' . $educationData->id,
            'education_data' => $educationData,
            'application' => $application,
            'canDeleteFile' => $allowDeleteFileFromEducationAfterApprove,
            'addNewFile' => $allowAddNewFileToEducationAfterApprove,
            'hideProfileFieldForEducation' => $hideProfileFieldForEducation,
            'has_pending_contractor' => $has_pending_contractor
        ]
    ); ?>
<?php endforeach; ?>

<?php
$canCreate = $canEdit || $allowAddNewEducationAfterApprove;
if ($canCreate) {
    $educationData = new EducationData();
    $postfix = 'new';

    echo $this->render(
        '_education_modal',
        [
            'postfix' => $postfix,
            'title' => Yii::t(
                'abiturient/bachelor/education/education-modal',
                'Заголовок модального окна обработки образования на странице док. об образ.: `Создать`'
            ),
            'canEdit' => true,
            'modal_id' => 'create_education_data',
            'education_data' => $educationData,
            'application' => $application,
            'hideProfileFieldForEducation' => $hideProfileFieldForEducation,
        ]
    );
} ?>

<?php Pjax::end();
