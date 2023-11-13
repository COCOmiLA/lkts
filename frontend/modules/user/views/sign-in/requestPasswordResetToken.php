<?php

use frontend\modules\user\models\PasswordResetRequestForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use kartik\form\ActiveForm;







$this->title = Yii::t(
    'sign-in/request-password-reset/form',
    'Заголовок формы восстановления пароля: `Запрос сброса пароля`'
);
$this->params['breadcrumbs'][] = $this->title;

?>

<style>
    .info {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    .info-block {
        border-left: 1px solid var(--light);
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    .recover-btn {
        float: right;
    }

    .info-block p {
        color: var(--gray);
        margin: auto 0;
    }

    @media screen and (max-width: 768px) {
        .info {
            display: block;
        }

        .info-block {
            border-left: none;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
        }

        .recover-btn {
            float: none;
        }
    }
</style>

<div class="site-request-password-reset">
    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <div class="row info">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

            <?= $form->field($model, 'email', ['errorOptions' => ['class' => 'form-text text-muted', 'encode' => false]])
                ->textInput(['type' => 'email']) ?>

            <div class="form-group">
                <?= Html::submitButton(
                    Yii::t(
                        'sign-in/request-password-reset/form',
                        'Надпись на кнопке для сохранения формы восстановления пароля: `Отправить`'
                    ),
                    ['class' => 'btn btn-primary recover-btn']
                ) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="col-lg-7 info-block">
            <p>
                <?= Yii::t(
                    'sign-in/request-password-reset/form',
                    'Поясняющее сообщение с боку формы восстановления пароля: `Если у вас нет доступа к почте или вы не регистрировались через этот личный кабинет, вы можете <a href="{url}"> восстановить доступ.</a>`',
                    ['url' => Url::toRoute(['/user/sign-in/abiturient-access'])]
                ) ?>
            </p>
        </div>
    </div>
</div>