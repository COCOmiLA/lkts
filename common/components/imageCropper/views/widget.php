<?php

use yii\web\View;
use common\components\imageCropper\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Html;








?>

<div class="cropper-widget">
    <?= Html::activeHiddenInput($model, $widget->attribute, ['class' => 'photo-field']); ?>

    <?= Html::hiddenInput('width', $widget->width, ['class' => 'width-input']); ?>

    <?= Html::hiddenInput('height', $widget->height, ['class' => 'height-input']); ?>

    <div class="row mb-2">
        <?php if ($widget->isPdfFile && $model->{$widget->attribute} == '') : ?>
            <iframe src="<?= $widget->noPhotoImage; ?>" width="100%" height="<?= $widget->thumbnailHeight; ?>px;"></iframe>
        <?php else : ?>
            <div class="col-12 col-lg-6 d-flex justify-content-center">
                <?= Html::img(
                    $model->{$widget->attribute} != ''
                        ? $model->{$widget->attribute}
                        : $widget->noPhotoImage,
                    [
                        'style' => "max-height: {$widget->thumbnailHeight}px;",
                        'class' => 'thumbnail',
                        'data-no-photo' => $widget->noPhotoImage
                    ]
                ); ?>
            </div>
        <?php endif; ?>

        <div class="col-12 col-lg-6">
            <?php if (!$widget->isReadonly) : ?>
                <div class="new-photo-area d-flex justify-content-center" style="height: <?= $widget->cropAreaHeight; ?>px;">
                    <div class="cropper-label">
                        <span><?= $widget->label; ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$widget->isReadonly) : ?>
        <div class="cropper-buttons btn-group btn-block mb-2" role="group">
            <button type="button" class="btn btn-sm btn-danger delete-photo" aria-label="<?= Yii::t('cropper', 'DELETE_PHOTO: `Удалить фото`'); ?>">
                <i class="fa fa-trash" aria-hidden="true"></i>

                <?= Yii::t('cropper', 'DELETE_PHOTO: `Удалить фото`'); ?>
            </button>

            <button type="button" class="btn btn-sm btn-success crop-photo hidden" aria-label="<?= Yii::t('cropper', 'CROP_PHOTO: `Обрезать фото`'); ?>">
                <i class="fa fa-scissors" aria-hidden="true"></i>

                <?= Yii::t('cropper', 'CROP_PHOTO: `Обрезать фото`'); ?>
            </button>

            <button type="button" class="btn btn-sm btn-info upload-new-photo hidden" aria-label="<?= Yii::t('cropper', 'UPLOAD_ANOTHER_PHOTO: `Загрузить другое фото`'); ?>">
                <i class="fa fa-picture-o" aria-hidden="true"></i>

                <?= Yii::t('cropper', 'UPLOAD_ANOTHER_PHOTO: `Загрузить другое фото`'); ?>
            </button>
        </div>
    <?php endif; ?>

    <div class="progress d-none">
        <div class="progress-bar bg-striped bg-success progress-bar-animated" role="progressbar">
            <span class="sr-only"></span>
        </div>
    </div>
</div>