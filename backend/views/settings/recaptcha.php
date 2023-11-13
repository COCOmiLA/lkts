<?php

use common\models\Recaptcha;
use common\models\RecaptchaForm;
use kartik\widgets\SwitchInput;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;








$pathToSvg = FileHelper::normalizePath('/frontend/web/install/img/RecaptchaLogo.svg');

$this->title = 'Настройки reCAPTCHA ' . Html::img($pathToSvg, ['width' => "22", 'height' => "22"]); ?>

<?php if (empty($recaptchas)) : ?>
    <?= Html::tag(
        'div',
        "Используемая вами база данных устарела; корректная работа функции reCAPTCHA невозможна.
            Для устранения, необходимо произвести обновления на странице
            <a href=" . Url::to(['update/index']) . ">Настройки личного кабинета поступающего → Обновление</a>",
        ['class' => 'alert alert-danger']
    ); ?>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-12">
        <?= $form->field($recaptchaForm, 'site_key_v2')->textInput(['value' => $recaptchaForm->site_key_v2]); ?>
        <?= $form->field($recaptchaForm, 'server_key_v2')->textInput(['value' => $recaptchaForm->server_key_v2]); ?>
        <?= $form->field($recaptchaForm, 'site_key_v3')->textInput(['value' => $recaptchaForm->site_key_v3]); ?>
        <?= $form->field($recaptchaForm, 'server_key_v3')->textInput(['value' => $recaptchaForm->server_key_v3]); ?>
    </div>
</div>

<div class="row">
    <?php foreach ($recaptchas as $recaptcha) : ?>
        <?php  ?>

        <?php $I = $recaptcha->id; ?>
        <div class="col-6">
            <?= $form->field($recaptcha, "[{$I}]version")
                ->widget(
                    SwitchInput::class,
                    [
                        'type' => SwitchInput::RADIO,
                        'items' => Recaptcha::radioItems(),
                        'pluginOptions' => ['size' => 'mini'],
                    ]
                ); ?>
        </div>
    <?php endforeach; ?>
</div>

<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']); ?>
<?php ActiveForm::end();