<?php

use common\models\attachment\attachmentCollection\BaseAttachmentCollection;
use common\models\EmptyCheck;
use common\models\interfaces\FileToShowInterface;
use common\modules\abiturient\models\repositories\FileRepository;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

$attachments = ArrayHelper::index($attachments, null, 'attachmentType.documentSetRef.reference_name');
$performRegulation = $performRegulation ?? false;
$minify = $minify ?? false;

$hasPassedApplication = false;
if ($app) {
    $hasPassedApplication = $app->hasPassedApplication();
}

$multiple = $multiple ?? true;






?>

<!--Этот partial обёрнут в row-->
<div class="col-12">
    <div class="card mb-3">
        <div class="card-header">
            <h4>
                <?= $formName ?? Yii::t(
                    'abiturient/attachment-widget',
                    'Дефолтный заголовок блока скан-копий виджета сканов: `Скан-копии документов`'
                ) ?>
            </h4>
        </div>

        <div class="card-body">
            <?php foreach ($attachments as $document_set_name => $attachments_in_set) : ?>
                <?php if (!is_array($attachments_in_set)) {
                    $attachments_in_set = [$attachments_in_set];
                }
                FileRepository::SortCollection($attachments_in_set);
                if (array_filter($attachments_in_set, function (BaseAttachmentCollection $c) {
                    return !$c->isHidden();
                })) : ?>
                    <?php ob_start(); ?>
                    <?php foreach ($attachments_in_set as $attachment) : ?>
                        <?php $calculated_is_required = !$isReadonly &&
                            $attachment->isRequired()
                            && (!ArrayHelper::getValue($attachment, 'attachmentType.need_one_of_documents')
                                
                                || !array_filter($attachments_in_set, function (BaseAttachmentCollection $c) {
                                    return !!$c->attachments;
                                }));
                        ?>
                        <?php if (!$attachment->isHidden()) : ?>
                            <?= $this->render('_attachment', [
                                'attachment' => $attachment,
                                'isReadonly' => $isReadonly,
                                'minify' => $minify,
                                'performRegulation' => $performRegulation,
                                'isRequired' => $calculated_is_required,
                                'hasPassedApplication' => $hasPassedApplication,
                                'multiple' => $multiple
                            ]); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php $content = ob_get_clean(); ?>
                    <?php if ($document_set_name) : ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4>
                                    <?= $document_set_name ?>
                                </h4>
                            </div>

                            <div class="card-body">
                                <?= $content ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <?= $content ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (isset($isForm) && $isForm == true) : ?>
            <div class="card-footer" style="text-align: right">
                <?= Html::submitButton(
                    Yii::t(
                        'abiturient/attachment-widget',
                        'Подпись кнопки сабмита виджета сканов: `Сохранить`'
                    ),
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($submit_block) && !EmptyCheck::isEmpty($submit_block)) : ?>
        <div class="row" style="margin-bottom: 25px;">
            <?= $submit_block; ?>
        </div>
    <?php endif; ?>
</div>