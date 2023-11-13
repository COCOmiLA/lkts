<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]); ?>

<?php echo $form->field($model, 'id') ?>

<?php echo $form->field($model, 'username') ?>

<?php echo $form->field($model, 'auth_key') ?>

<?php echo $form->field($model, 'email')
    ->textInput(['type' => 'email']) ?>

<?php echo $form->field($model, 'role') ?>

<?php echo $form->field($model, 'status') ?>

<?php echo $form->field($model, 'created_at') ?>

<?php echo $form->field($model, 'updated_at') ?>

<div class="form-group">
    <?php echo Html::submitButton(Yii::t('backend', 'Поиск'), ['class' => 'btn btn-primary']) ?>
    <?php echo Html::resetButton(Yii::t('backend', 'Сбросить'), ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end();