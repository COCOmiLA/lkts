<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;





$this->title = Yii::t('frontend', 'Регистрация');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
            <?php echo $form->field($model, 'username') ?>
            <?php echo $form->field($model, 'email')
                ->textInput(['type' => 'email']) ?>
            <?php echo $form->field($model, 'password')
                ->passwordInput() ?>
            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('frontend', 'Регистрация'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>