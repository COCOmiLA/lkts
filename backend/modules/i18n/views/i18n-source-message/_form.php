<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<div class="i18n-source-message-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'category')->textInput(['maxlength' => 32]) ?>

    <?php echo $form->field($model, 'message')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Создать') : Yii::t('backend', 'Редактировать'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
