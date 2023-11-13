<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;





$this->title = Yii::t('frontend', 'Вход');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <?php echo $form->field($model, 'identity')
                ->textInput(['type' => 'email']) ?>
            <?php echo $form->field($model, 'password')->passwordInput() ?>

            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('frontend', 'Вход'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>