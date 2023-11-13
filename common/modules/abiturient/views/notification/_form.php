<?php

use common\models\notification\NotificationForm;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;


?>

<?php $form = ActiveForm::begin([
    'id' => 'notification-form',
    'action' => Url::toRoute('notification/send'),
    'options' => [
        'enctype' => 'multipart/form-data',
        'data-pjax' => "0"
    ],
]) ?>

<div class="alert alert-danger" id="notification-errors" style="display:none">
    <ul></ul>
</div>

<div class="row">
    <div class="col-12">
        <?php echo $form->field($model, 'title'); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php echo Yii::t('notification/index/form', 'Подпись числа выбранных получателей; на стр. рассылки уведомлений: `Выбрано получателей`') ?>:
        <span class="badge badge-pill receiver_count">0</span><br>
    </div>
</div>

<div class="row mb-2">
    <div class="col-12">
        <?php echo $form->field($model, 'body')->textarea([
            'rows' => 10,
            'class' => 'form-control notification-body'
        ]); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php echo FileInput::widget([
            'model' => $model,
            'attribute' => 'attachments[]',
            'id' =>  "notification_attachment",
            'options'=>[
                'multiple'=>true
            ],
            'pluginOptions' => [
                'theme' => 'fa4',
                'initialPreviewAsData'=>true,
                'overwriteInitial'=>false,
                'maxFileSize'=>2800,
                'showPreview' => true,
                'showUpload' => false,
                'showRemove' => true,
                'showClose' => false,
            ]
        ]); ?>
    </div>
</div>

<div class="row form-group-notification">
    <div class="col-12">
        <?php echo Html::submitButton(
            Yii::t('notification/index/form', 'Подпись кнопки для отправки уведомлений; на стр. рассылки уведомлений: `Отправить`'),
            ['class' => 'btn btn-primary float-right']
        ); ?>
    </div>
</div>

<div id="hidden_inputs"></div>

<?php ActiveForm::end();
