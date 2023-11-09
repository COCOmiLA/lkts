<?php

use common\models\notification\NotificationSetting;
use common\models\notification\NotificationType;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;








$this->title = Yii::t('backend', 'Настройки уведомлений');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin([
    'fieldConfig' => [
        'template' => "{input}\n{error}"
    ]
]); ?>

<div class="card mb-3">
    <div class="card-header">
        <h4>
            <?php echo Yii::t('backend', 'Доступные способы доставки уведомлений'); ?>
        </h4>
    </div>
    <div class="card-body">
        <?php foreach ($types as $key => $type) : ?>
            <?php echo $form->field($type, "[$key]enabled")->checkbox([
                'label' => $type->description
            ]); ?>
        <?php endforeach; ?>
    </div>
</div>

<label for="request_interval">
    <?php echo Yii::t('backend', 'Виджет уведомлений') ?>
</label>
<?php echo $form->field($enable_widget, 'value')->widget(SwitchInput::class, [
    'options' => [
        'id' => 'enable_widget',
        'name' => 'enable_widget'
    ]
]); ?>

<label for="request_interval">
    <?php echo Yii::t('backend', 'Периодичность опроса (сек)') ?>
</label>
<?php echo $form->field($request_interval, 'value')->textInput([
    'id' => 'request_interval',
    'name' => 'request_interval',
    'type' => 'number',
    'min' => NotificationSetting::MIN_REQUEST_INTERVAL,
    'max' => NotificationSetting::MAX_REQUEST_INTERVAL
]); ?>

<?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary float-right']); ?>

<?php ActiveForm::end();
