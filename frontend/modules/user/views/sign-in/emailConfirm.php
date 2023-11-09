<?php

use common\models\UserRegistrationConfirmToken;
use frontend\modules\user\models\EmailCodeConfirmForm;
use yii\bootstrap4\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;

$this->title = $this->title = Yii::t(
    'sign-in/confirm-email/form',
    'Заголовок страницы подтверждения почты: `Подтверждение email`'
);
$this->params['breadcrumbs'][] = $this->title;





?>

<style>
    .code-input input {
        width: 35px;
    }

    .code-input {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
    }
</style>

<div class="site-login">
    <h2>
        <?= Yii::t(
            'sign-in/confirm-email/form',
            'Заголовок формы; страницы подтверждения почты: `Для завершения регистрации необходимо подтвердить email.`'
        ) ?>
    </h2>

    <div class="row">
        <div class="col-12">
            <?php $form = ActiveForm::begin(['method' => 'post']); ?>

            <div class="row">
                <div class="col-12 col-md-6 col-lg-3">
                    <?php $codeLength = UserRegistrationConfirmToken::CODE_LENGTH;
                    echo $form->field($model, "code")->widget(MaskedInput::class, [
                        'mask' => "+{0,{$codeLength}}",
                        'definitions' => ['+' => [
                            'validator' => "[0-9A-Za-z]",
                            'cardinality' => 1,
                        ]],
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= Alert::widget([
                        'options' => ['class' => 'alert-info'],
                        'body' => Yii::t(
                            'sign-in/confirm-email/form',
                            'Текст информационного алерта; страницы подтверждения почты: `Код подтверждения отправлен на e-mail, указанный при регистрации.`'
                        ),
                    ]) ?>
                </div>
            </div>

            <div class="row" style="margin-bottom: 12px">
                <div class="col-12">
                    <?= Html::a(
                        Yii::t(
                            'sign-in/confirm-email/form',
                            'Подпись ссылки для повторного отправки кода-подтверждения; страницы подтверждения почты: `Отправить новый код на мой email.`'
                        ),
                        Url::toRoute(['/user/sign-in/repeat-email-confirm'])
                    ); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?php echo Html::submitButton(
                        Yii::t(
                            'sign-in/confirm-email/form',
                            'Подпись кнопки для сохранения формы; страницы подтверждения почты: `Подтвердить`'
                        ),
                        [
                            'class' => 'btn btn-primary'
                        ]
                    ) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-12">
            <?php if (Yii::$app->session->hasFlash('alert-email-confirm')) {
                echo Alert::widget([
                    'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-email-confirm'), 'body'),
                    'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-email-confirm'), 'options'),
                ]);
            } ?>
        </div>
    </div>
</div>