<?php

use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use yii\bootstrap4\Modal;
use yii\helpers\Html;



$form_id = "reject-enrollment-modal-form-{$bachelor_speciality->id}";
$disabled = false;
$isReadonly = false;
?>

<?php Modal::begin([
    'id' => "reject-enrollment-modal-{$bachelor_speciality->id}",
    'title' => Html::tag(
        'h4',
        Yii::t(
            'abiturient/applications/reject-enrollment-modal',
            'Заголовок модального окна отзыва заявления на странице заявлений: `Отказаться от зачисления`'
        )
    ),
    'size' => 'modal-lg',
]);

echo Html::beginForm($url, 'POST', [
    'id' => $form_id,
    'enctype' => 'multipart/form-data'
]); ?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-warning" role="alert">
            <?= Html::a(
                Yii::t(
                    'abiturient/applications/reject-enrollment-modal',
                    'Подпись ссылки скачивания печатной формы заявления на отзыв; модального окна отзыва заявления на странице заявлений: `Скачать бланк для отказа от зачисления`'
                ),
                [
                    'bachelor/print-enrollment-rejection-form',
                    'bachelor_spec_id' => $bachelor_speciality->id,
                ],
                [
                    'target' => '_blank'
                ]
            ); ?>
        </div>
    </div>

    <div class="col-12 form-group">
        <?php foreach ($attachments as $attachment) : ?>
            <div class="form-group">
                <label class="col-form-label" style="overflow-wrap: break-word;">
                    <?= $attachment->getAttachmentTypeName() ?>
                </label>
            </div>
            <?php echo $this->render('@abiturient/views/partial/fileInput/_fileInput', [
                'attachmentCollection' => $attachment,
                'isReadonly' => false,
                'required' => true,
                'container_id' => "attachment-{$attachment->attachmentType->id}-{$bachelor_speciality->id}",
                'model' => $attachment->getModelEntity(),
                'multiple' => false,
                'id' => "{$attachment->getModelEntity()->formName()}{$attachment->getIndex()}_{$bachelor_speciality->id}"
            ]); ?>
        <?php endforeach; ?>
    </div>

    <div class="col-12 form-group">
        <?php $btnName = Yii::t(
            'abiturient/applications/reject-enrollment-modal',
            'Подпись кнопки сохранения; модального окна отзыва заявления на странице заявлений: `Сохранить`'
        ) ?>
        <input type="submit" class="btn btn-primary float-right" value="<?= $btnName ?>" />
    </div>
</div>

<?php
echo Html::endForm();
Modal::end();
