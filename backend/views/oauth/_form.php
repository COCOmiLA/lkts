<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<div class="oauth-form card-body">

    <?php $form = ActiveForm::begin(); ?>
        <?php echo $form->field($model, 'client_id') ?>
        <?php echo $form->field($model, 'client_secret') ?>
        <?php echo $form->field($model, 'redirect_uri') ?>
        <?php echo $form->field($model, 'grant_types')->textInput(['readonly' => true]) ?>
    
        <div class="form-group">
            <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'oauth-button']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div>
