<?php

use backend\models\MainPageInstructionImage;
use kartik\form\ActiveForm;
use yii\web\View;








$indexWidth      = 'width';
$indexHeight     = 'height';
$indexUploadFile = 'file';
if ($model->main_page_setting_id) {
    $model->file = '';
    if ($linkedFile = $model->linkedFile) {
        $model->file = $linkedFile->upload_name;
    }

    $indexWidth      = "[{$model->main_page_setting_id}]{$indexWidth}";
    $indexHeight     = "[{$model->main_page_setting_id}]{$indexHeight}";
    $indexUploadFile = "[{$model->main_page_setting_id}]{$indexUploadFile}";
}

?>

<div class="row">
    <div class="col-12">
        <?= $form->field($model, $indexUploadFile)
            ->fileInput(
                ['accept' => MainPageInstructionImage::ACCEPT_FILE_EXTENSIONS]
            ); ?>
    </div>
</div>

<div class="row">
    <div class="col-12 col-sm-6">
        <?= $form->field($model, $indexWidth)
            ->textInput(['type' => 'number']); ?>
    </div>

    <div class="col-12 col-sm-6">
        <?= $form->field($model, $indexHeight)
            ->textInput(['type' => 'number']); ?>
    </div>
</div>

<?php if ($model->main_page_setting_id) : ?>
    <div class="row">
        <div class="col-12">
            <img
                alt="<?= $model->file ?>"
                width="<?= $model->width ?>"
                height="<?= $model->height ?>"
                src="<?= $model->buildSourceUrl() ?>"
            >
        </div>
    </div>
<?php endif;