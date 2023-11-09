<?php





use common\components\keyStorage\FormWidget;

$this->title = Yii::t('backend', 'Настройки приложения');

?>

<div class="card">
    <div class="card-body">
        <?= FormWidget::widget([
            'model' => $model,
            'formClass' => '\kartik\form\ActiveForm',
            'submitText' => Yii::t('backend', 'Сохранить'),
            'submitOptions' => ['class' => 'btn btn-primary']
        ]); ?>
    </div>
</div>