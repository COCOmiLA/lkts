<?php

use common\components\ini\iniGet;
use common\models\interfaces\FileToShowInterface;
use common\widgets\TooltipWidget\TooltipWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;








$multiple = $multiple ?? true;

$i = $attachment->getIndex();
$model = $attachment->getModelEntity();
$performRegulation = $performRegulation ?? false;

$isRequired = $isRequired ?? (!$isReadonly && $attachment->isRequired());
$container_id = "attachment-{$attachment->attachmentType->id}";
$minify = $minify ?? false;

$addNewFile = true;
$canDeleteFile = true;
if (
    $isReadonly &&
    (
        $hasPassedApplication ||
        (
            isset($attachment->questionary) &&
            $attachment->questionary->hasApprovedApps()
        )
    )
) {
    $isReadonly = false;
    $addNewFile = ArrayHelper::getValue($attachment, 'attachmentType.allow_add_new_file_after_app_approve', false);
    $canDeleteFile = ArrayHelper::getValue($attachment, 'attachmentType.allow_delete_file_after_app_approve', false);

    if (!$addNewFile && !$canDeleteFile) {
        
        $addNewFile = true;
        $isReadonly = true;
        $canDeleteFile = true;
    }
}

?>

<div class="row">
    <div class="col-12 <?= $isRequired ? 'required' : '' ?>" id="<?php echo $container_id; ?>">
        <div class="row">
            <div class="col-12 col-md-3">
                <label class="col-form-label <?= $isRequired ? 'has-star' : '' ?>" style="overflow-wrap: break-word;">
                    <?php $attachmentTypeLabel = $attachment->getAttachmentTypeName();
                    $attachmentTypeTemplate = ArrayHelper::getValue($attachment, 'attachmentType.attachmentTypeTemplate');
                    if ($attachmentTypeTemplate) {
                        $attachmentTypeLabel = $this->render(
                            '_modal_attachment_template_preview',
                            compact([
                                'attachmentTypeLabel',
                                'attachmentTypeTemplate',
                            ])
                        );
                    } ?>
                    <?= $attachmentTypeLabel ?>

                    <?= TooltipWidget::widget([
                        'message' => ArrayHelper::getValue($attachment, 'attachmentType.tooltip_description')
                    ]) ?>
                </label>
            </div>

            <div class="col-12 col-md-9">
                <?= $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                    'attachmentCollection' => $attachment,
                    'isReadonly' => $isReadonly,
                    'required' => $isRequired,
                    'container_id' => $container_id,
                    'model' => $model,
                    'minify' => $minify,
                    'addNewFile' => $addNewFile,
                    'canDeleteFile' => $canDeleteFile,
                    'multiple' => $multiple
                ]); ?>

                <?php foreach ($attachment->getSendingProperties() as $property => $value) : ?>
                    <?= Html::hiddenInput("{$model->formName()}[{$i}][{$property}]", $value); ?>
                <?php endforeach; ?>

                <?php if ($performRegulation) : ?>
                    <?= Html::hiddenInput("{$model->formName()}[{$i}][regulation]", '1'); ?>
                <?php endif; ?>

                <?php if (!empty($attachment->getAttachmentsErrors())) : ?>
                    <div class="alert alert-danger" style="margin: 15px 0">
                        <p>
                            <?= Yii::t(
                                'abiturient/attachment-widget',
                                'Тело ошибки виджета сканов: `Ошибка:`'
                            ); ?>
                        </p>

                        <?php foreach ($attachment->getAttachmentsErrors() as $fileName => $errors) : ?>
                            <p>
                                <strong>
                                    <?= $fileName ?>
                                </strong>
                            </p>

                            <ul style="margin-left: 24px">
                                <?php foreach ($errors as $errorAttr => $attrErrors) : ?>
                                    <?php foreach ($attrErrors as $attrError) : ?>
                                        <li>
                                            <span>
                                                <?= $attrError ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <span class="form-text text-muted" style="padding-left: 0;">
                    <?= Yii::t(
                        'abiturient/attachment-widget',
                        'Текст сообщения об максимально допустимом размере файла виджета сканов: `Максимальный размер приложенного файла: {uploadMaxFilesizeString}`',
                        ['uploadMaxFilesizeString' => iniGet::getUploadMaxFilesizeString()]
                    ); ?>
                </span>
            </div>
        </div>
    </div>
</div>