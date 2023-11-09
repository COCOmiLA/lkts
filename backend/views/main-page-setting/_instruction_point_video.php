<?php

use backend\models\MainPageInstructionVideo;
use kartik\form\ActiveForm;
use yii\web\View;








$indexLoop       = 'loop';
$indexMuted      = 'muted';
$indexWidth      = 'width';
$indexHeight     = 'height';
$indexAutoplay   = 'autoplay';
$indexUploadFile = 'file';
if ($model->main_page_setting_id) {
    $model->file = '';
    if ($linkedFile = $model->linkedFile) {
        $model->file = $linkedFile->upload_name;
    }

    $indexLoop       = "[{$model->main_page_setting_id}]{$indexLoop}";
    $indexMuted      = "[{$model->main_page_setting_id}]{$indexMuted}";
    $indexWidth      = "[{$model->main_page_setting_id}]{$indexWidth}";
    $indexHeight     = "[{$model->main_page_setting_id}]{$indexHeight}";
    $indexAutoplay   = "[{$model->main_page_setting_id}]{$indexAutoplay}";
    $indexUploadFile = "[{$model->main_page_setting_id}]{$indexUploadFile}";
}

?>

<div class="row">
    <div class="col-12">
        <?= $form->field($model, $indexUploadFile)
            ->fileInput(
                ['accept' => MainPageInstructionVideo::ACCEPT_FILE_EXTENSIONS]
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

<div class="row">
    <div class="col-12 col-sm-4">
        <?= $form->field($model, $indexLoop)
            ->checkbox(); ?>
    </div>

    <div class="col-12 col-sm-4">
        <?= $form->field($model, $indexMuted)
            ->checkbox(); ?>
    </div>

    <div class="col-12 col-sm-4">
        <?= $form->field($model, $indexAutoplay)
            ->checkbox(); ?>
    </div>
</div>

<?php if ($model->main_page_setting_id) : ?>
    <div class="row">
        <div class="col-12">
            <video
                controls
                width="<?= $model->width ?>"
                height="<?= $model->height ?>"
                <?= $model->buildAdditionalHtmlAttributes() ?>
            >
                <source
                    src="<?= $model->buildSourceUrl() ?>"
                    type="video/<?= $model->extensions ?>"
                >

                <?= Yii::t(
                    'abiturient/download-instruction-attachment',
                    'Текст сообщения о невозможности воспроизвести видео для инструкции поступающего: `Ваш браузер не поддерживает воспроизведение видео.`'
                ) ?>
            </video>
        </div>
    </div>
<?php endif;