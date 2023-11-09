<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<div class="i18n-message-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'id')->textInput(['disabled'=>!$model->isNewRecord]) ?>

    <?php if (!$model->isNewRecord): ?>
        <?php echo $form->field($model, 'category')->textInput(['disabled'=>true]) ?>
        <?php echo $form->field($model, 'sourceMessage')->textInput(['disabled'=>true]) ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'language')->textInput(['maxlength' => 16]) ?>

    <?php echo $form->field($model, 'translation')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Создать') : Yii::t('backend', 'Редактировать'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
