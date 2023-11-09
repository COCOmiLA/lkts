<?php

use common\models\Recaptcha;
use frontend\modules\user\models\LoginForm;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








$this->title = Yii::t(
    'sign-in/login/form',
    'Заголовок страницы авторизации: `Вход`'
);

if (!isset($error)) $error = '';
if (!isset($isAbit)) $isAbit = false;


if ($error == 'emptyRoles') {
    echo Html::tag(
        'div',
        Yii::t(
            'sign-in/login/form',
            'Шаблон тела алерта об ошибке на форме авторизации: `<strong>Ошибка входа в систему:</strong> {message}`',
            ['message' => Yii::t(
                'sign-in/login/form',
                'Сообщение об ошибке на форме авторизации: `нет доступных ролей.`'
            )]
        ),
        ['class' => 'alert alert-danger', 'role' => 'alert']
    );
} elseif ($error == 'emptyRecordbooks') {
    echo Html::tag(
        'div',
        Yii::t(
            'sign-in/login/form',
            'Шаблон тела алерта об ошибке на форме авторизации: `<strong>Ошибка входа в систему:</strong> {message}`',
            ['message' => Yii::t(
                'sign-in/login/form',
                'Сообщение об ошибке на форме авторизации: `нет данных об обучении.`'
            )]
        ),
        ['class' => 'alert alert-danger', 'role' => 'alert']
    );
}



if ($error == 'emptyRolesAbiturienta') {
    echo Html::tag(
        'div',
        Yii::t(
            'sign-in/login/form',
            'Шаблон тела алерта об ошибке на форме авторизации: `<strong>Ошибка входа в систему:</strong> {message}`',
            ['message' => Yii::t(
                'sign-in/login/form',
                'Сообщение об ошибке на форме авторизации: `нет доступных ролей.`'
            )]
        ),
        ['class' => 'alert alert-danger', 'role' => 'alert']
    );
} elseif ($error == 'emptyRoleRule') {
    echo Html::tag(
        'div',
        Yii::t(
            'sign-in/login/form',
            'Шаблон тела алерта об ошибке на форме авторизации: `<strong>Ошибка входа в систему:</strong> {message}`',
            ['message' => Yii::t(
                'sign-in/login/form',
                'Сообщение об ошибке на форме авторизации: `отсутствует таблица ролей.<br/>Обратитесь к администратору.`'
            )]
        ),
        ['class' => 'alert alert-danger', 'role' => 'alert']
    );
}


$this->params['breadcrumbs'][] = $this->title;

?>

<div class="site-login">
    <h1>
        <?= Yii::t(
            'sign-in/login/form',
            'Заголовок формы авторизации: `Личный кабинет`'
        ); ?>
    </h1>


    <?php if ($access == "1") : ?>
        <div class="alert alert-success" role="alert">
            <p>
                <?= Yii::t(
                    'sign-in/login/form',
                    'Алерт информирующий об успешном создании пароля на форме авторизации: `Пароль создан успешно, вы можете авторизоваться`'
                ) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($loginTopText = Yii::$app->configurationManager->getText('login_top_text')) : ?>
        <div class="alert alert-info" role="alert">
            <?= $loginTopText; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'identity'); ?>

            <?= $form->field($model, 'password')->passwordInput(); ?>

            <?php $widgetParams = Recaptcha::getWidgetParamsByName('login'); ?>
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

            <?php if (Yii::$app->configurationManager->getAllowRememberMe()) : ?>
                <?= $form->field($model, 'rememberMe')->checkbox(); ?>
            <?php endif; ?>

            <?php if ($model->hasErrors('password')) : ?>
                <div style="color:var(--gray);margin:1em 0">
                    <?= Yii::t(
                        'sign-in/login/form',
                        'Подпись ссылки восстановления пароля на форме авторизации: `Если вы забыли пароль, вы можете сбросить его <a href="{link}">здесь</a>`',
                        ['link' => Url::to(['sign-in/request-password-reset'])]
                    ) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <?= Html::submitButton(
                    Yii::t(
                        'sign-in/login/form',
                        'Подпись кнопки для сохранения формы авторизации: `Вход`'
                    ),
                    ['class' => 'btn btn-primary', 'name' => 'login-button']
                ) ?>
            </div>

            <?php if ($isAbit) : ?>
                <div class="form-group">
                    <?= Html::a(Yii::$app->configurationManager->getText('register_link_text'), ['abiturient-signup']) ?>
                </div>

                <div class="form-group">
                    <?= Html::a(Yii::$app->configurationManager->getText('createacc_link_text'), ['request-password-reset']); ?>
                </div>
            <?php endif; ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if ($loginBottomText = Yii::$app->configurationManager->getText('login_bottom_text')) : ?>
        <div class="alert alert-info" role="alert">
            <?= $loginBottomText; ?>
        </div>
    <?php endif; ?>
</div>