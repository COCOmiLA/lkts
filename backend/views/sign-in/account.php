<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;





$this->title = Yii::t('backend', 'Редактировать аккаунт')
?>

<div class="user-profile-form card-body">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'username') ?>

    <?php echo $form->field($model, 'email')
        ->textInput(['type' => 'email']) ?>

    <?php echo $form->field($model, 'password')->passwordInput() ?>

    <?php echo $form->field($model, 'password_confirm')->passwordInput() ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Редактировать'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>