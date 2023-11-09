<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;





$this->title = Yii::t('backend', 'Вход');
$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'login-page';
?>
<div class="login-card card-body">
    <div class="login-logo">
        <?php echo Html::encode($this->title) ?>
    </div><!-- /.login-logo -->
    <div class="header"></div>
    <div class="login-card-body">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <div class="body">
            <?php echo $form->field($model, 'username') ?>
            <?php echo $form->field($model, 'password')->passwordInput() ?>
            <?php if (Yii::$app->configurationManager->getAllowRememberMe()): ?>
                <?php echo $form->field($model, 'rememberMe')->checkbox(['class'=>'simple']) ?>
            <?php endif; ?>
        </div>
        <div class="footer">
            <?php echo Html::submitButton(Yii::t('backend', 'Войти'), [
                'class' => 'btn btn-primary btn-flat btn-block',
                'name' => 'login-button'
            ]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>