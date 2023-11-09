<?php

use common\components\FilesWorker\FilesWorker;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\repositories\FileRepository;
use common\services\abiturientController\sandbox\AllApplicationAttachmentsService;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








if (!isset($allow_remove)) {
    $allow_remove = false;
}
?>

<div class="card mb-3">
    <div class="card-header">
        <h4>
            <?= Yii::t(
                'sandbox/moderate/application-block/all',
                'Заголовок блока скан-копий; на стр. проверки анкеты поступающего: `Копии документов`'
            ) ?>
        </h4>
    </div>

    <div class="card-body">
        <?php $collections = $questionary->getEntireQuestionaryAttachmentCollections();
        $collections = array_merge($collections, $application->getEntireApplicationAttachmentCollections());
        FileRepository::SortCollection($collections); ?>
        <?php if ($collections) : ?>
            <?php foreach ($collections as $collection) : ?>
                <?php foreach ($collection->attachments as $attachment) : ?>
                    <?php if (ArrayHelper::getValue($attachment, 'attachmentType.hidden') == 0) : ?>
                        <?php $attachmentTypeName = $attachment->getAttachmentTypeName();
                        $attachment_url = Url::to(['site/download', 'id' => $attachment->id]);
                        $is_previewable = in_array($attachment->extension, FilesWorker::getAllowedImageExtensionsToUploadList()); ?>
                        <div class="row">
                            <label class="col-sm-6 col-12 control-label" style="word-break: break-word;">
                                <?= $attachmentTypeName; ?>
                            </label>

                            <div class="col-sm-6 col-12">
                                <?php if ($is_previewable) : ?>
                                    <?php $tooltip_title = "<img src='{$attachment_url}' alt='{$attachmentTypeName}' />"; ?>
                                    <a
                                            class="tooltip_with_image"
                                            href="<?= $attachment_url ?>"
                                            target="_blank"
                                            data-html="true"
                                            data-toggle="tooltip"
                                            data-placement="right"
                                            title="<?= $tooltip_title ?>"
                                    >
                                        <?= $attachment->filename; ?>
                                    </a>
                                <?php else : ?>
                                    <a target="_blank" href="<?= $attachment_url ?>">
                                        <?= $attachment->filename; ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($allow_remove): ?>
                                    <?php
                                    echo Html::a(
                                        '<i class="fa fa-remove"></i>',
                                        $attachment->getFileDeleteUrl(true),
                                        [
                                            'data' => [
                                                'confirm' => 'Вы уверены что хотите удалить из заявления эту скан-копию?',
                                                'toggle' => 'tooltip',
                                                'placement' => 'top',
                                                'title' => 'Удалить'
                                            ],
                                        ]
                                    );
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <div class="row">
                <div class="col-12">
                    <?php if (!empty($collections)) {
                        if (empty($application)) {
                            $app = $questionary->id;
                            $type = AllApplicationAttachmentsService::TYPE_QUESTIONARY;
                        } else {
                            $app = $application->id;
                            $type = AllApplicationAttachmentsService::TYPE_APPLICATION;
                        }
                        $dowonloadAllBtn = Yii::t(
                            'sandbox/moderate/application-block/all',
                            'Подпись кнопки для скачивания всех файлов; на стр. проверки анкеты поступающего: `Скачать все файлы`'
                        );
                        echo Html::a(
                            '<i class="fa fa-download"></i>' . $dowonloadAllBtn,
                            Url::toRoute(['sandbox/get-all-attachments', 'id' => $app, 'type' => $type]),
                            [
                                'target' => '_blank',
                                'class' => 'btn btn-info'
                            ]
                        );
                    } ?>
                </div>
            </div>
        <?php else : ?>
            <p>
                <?= Yii::t(
                    'sandbox/moderate/application-block/all',
                    'Текст для пустого списка скан-копий; на стр. проверки анкеты поступающего: `Нет прикрепленных копий`'
                ) ?>
            </p>
        <?php endif; ?>
    </div>
</div>