<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]); ?>

<?php echo $form->field($model, 'id') ?>

<?php echo $form->field($model, 'level') ?>

<?php echo $form->field($model, 'category') ?>

<?php echo $form->field($model, 'log_time') ?>

<?php echo $form->field($model, 'prefix') ?>

<?php echo $form->field($model, 'message') ?>

<div class="form-group">
    <?php echo Html::submitButton(Yii::t('backend', 'Поиск'), ['class' => 'btn btn-primary']) ?>
    <?php echo Html::resetButton(Yii::t('backend', 'Сбросить'), ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end();
