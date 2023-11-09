<?php

use common\models\Recaptcha;
use frontend\modules\user\models\AccessForm;
use yii\helpers\Html;
use yii\web\View;
use kartik\form\ActiveForm;





$this->title = 'Восстановление доступа';
$this->params['breadcrumbs'][] = $this->title;

$alert = Yii::$app->session->getFlash('abiturientCodeExt_ErrorValidateEmail');
if ($alert) {
    echo Html::tag('div', $alert, ['class' => 'alert alert-danger', 'role' => 'alert']);
}
$creatingErrors = Yii::$app->session->getFlash('userFetchingFrom1CError');
if ($creatingErrors) {
    echo Html::beginTag('div', ['class' => 'alert alert-danger']);
    echo Html::tag('p', 'Ошибки валидации данных из 1С:');
    foreach ($creatingErrors as $type => $errors) {
        echo Html::tag('p', "<strong>{$type}</strong>");
        echo Html::beginTag('ul', [
            'style' => 'margin-left: 20px;  '
        ]);
        if (is_array($errors)) {
            foreach ($errors as $error) {
                if (is_array($error)) {
                    foreach ($error as $e) {
                        echo Html::tag('li', $e);
                    }
                } else {
                    echo Html::tag('li', $error);
                }
                echo Html::tag('li', $error);
            }
        } else {
            echo Html::tag('li', $errors);
        }
        echo Html::endTag('ul');
    }
    echo Html::endTag('div');
}
?>
<style>
    .info-input {
        border-left: 1px solid var(--light);
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    .info-input p {
        color: var(--gray);
        margin: auto 0;
    }

    .info-row {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    @media screen and (max-width: 768px) {
        .info-row {
            display: block;
        }

        .info-input {
            border-left: none;
            margin-bottom: 30px;
        }
    }
</style>
<div class="site-login">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['id' => 'access-form']); ?>
    <?php echo Html::hiddenInput('vt', (int)Yii::$app->configurationManager->signupEmailEnabled, ['id' => 'vt']); ?>
    <?php echo $form->field($model, 'user_ref')
        ->hiddenInput(['value' => $model->user_ref->id ?? null])
        ->label(false) ?>
    <div class="row info-row">
        <div class="col-sm-12 col-md-6">
            <?php echo $form->field($model, 'email', ['errorOptions' => ['class' => 'form-text text-muted', 'encode' => false]])
                ->textInput(['type' => 'email']) ?>
        </div>
        <div class="col-sm-12 col-md-6 info-input">
            <p>Этот email будет использоваться для регистрации. На него придет письмо с логином и паролем от вашего
                аккаунта.</p>
        </div>
    </div>
    <?php if (!Yii::$app->configurationManager->signupEmailEnabled) : ?>
        <div class="row info-row">
            <div class="col-md-6">
                <?php echo $form->field($model, 'password', ['options' => ['class' => 'form-group required']])
                    ->passwordInput() ?>
            </div>
            <div class="col-sm-12 col-md-6 info-input">
                <p>В дальнейшем этот пароль будет использоваться для входа в ваш кабинет.</p>
            </div>
        </div>
        <div class="row info-row">
            <div class="col-sm-12 col-md-6">
                <?php echo $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group required']])
                    ->passwordInput() ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-6 col-12">
            <?php $widgetParams = Recaptcha::getWidgetParamsByName('abit_access'); ?>
            <?php if (!empty($widgetParams)) : ?>
                <?= $form->field(
                    $model,
                    'reCaptcha',
                    ['template' => '{input}']
                )->widget(
                    $widgetParams['class'],
                    $widgetParams['settings']
                ); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-12">
            <?php if (!Yii::$app->configurationManager->signupEmailEnabled) : ?>
            <?php endif; ?>
            <div class="form-group">
                <?php echo Html::submitButton('Восстановить доступ', ['class' => 'btn btn-primary float-left', 'name' => 'access-button']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>