<?php

use common\modules\abiturient\assets\questionaryViewAsset\QuestionaryViewAsset;
use common\services\abiturientController\sandbox\AllApplicationAttachmentsService;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;







$this->title = Yii::$app->name . ' | ' . 'Просмотр анкет';

$template = "{input}\n{error}";

QuestionaryViewAsset::register($this);
?>

<div class="row">
    <div class="col-6">
        <h3>
            Создал анкету <?= $questionary->fio; ?>
        </h3>
    </div>

    <div class="col-6">
        <a href="<?= Url::toRoute(['sandbox/questionaries']); ?>" class="btn btn-primary float-right">
            Назад к списку анкет
        </a>
    </div>
</div>

<?= $this->render("partial/_questionary_view", [
    'questionary' => $questionary
]) ?>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    Копии документов
                </h4>
            </div>

            <div class="card-body">
                <?php if ($questionary->attachments) : ?>
                    <?php foreach ($questionary->attachments as $attachment) : ?>
                        <div class="form-group col-12">
                            <label class="col-sm-3 col-form-label">
                                <?= $attachment->attachmentType->name; ?>
                            </label>

                            <div class="col-sm-9">
                                <a class="file-link" target="_blank" href="<?= Url::to(['site/download', 'id' => $attachment->id]); ?>"><?= $attachment->filename; ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-group col-12">
                        <div class="col-sm-3">
                            <?php if (!empty($questionary->attachments[0]->questionary_id) || !empty($application->allAttachments[0]->application_id)) {
                                if (empty($application->allAttachments[0]->application_id)) {
                                    $app = $questionary->id;
                                    $type = AllApplicationAttachmentsService::TYPE_QUESTIONARY;
                                } else {
                                    $app = $application->id;
                                    $type = AllApplicationAttachmentsService::TYPE_APPLICATION;
                                }
                                echo Html::a(
                                    '<i class="fa fa-download"></i> Скачать все файлы',
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
                        Нет прикрепленных копий
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>