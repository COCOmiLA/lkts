<?php

use backend\assets\AuthAsset;
use cheatsheet\Time;
use common\models\settings\AuthSetting;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;







$this->title = Yii::t('backend', 'Настройки авторизации');
AuthAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'auth-form',
    'options' => ['name' => 'AuthForm'],
    'fieldConfig' => [
        'template' => "{input}\n{error}"
    ]
]); ?>

<?php echo $form->field($use_email, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Использовать email для создания пароля к аккаунту поступающего'),
        'name' => 'use_email'
    ]); ?>
<span class="span-hint">
    <?php echo Yii::t('backend', '(в случае включения этой опции поступающий, подавший заявление сможет получить пароль от аккаунта на почту, в случае отключения - потребуется ввести последние 4 цифры паспорта в поле "Секретный код" и после этого создать пароль.)') ?>
</span>

<?= $form->field($canNotInputLatinFio, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Ограничить ввод ФИО при регистрации кириллическими символами'),
        'name' => 'canNotInputLatinFio'
    ]); ?>

<?= $form->field($confirmPassword, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Требовать подтверждение пароля при регистрации'),
        'name' => 'confirmPassword',
    ]); ?>

<label for="token-ttl">
    <?php echo Yii::t('backend', 'Минимальная длина пароля') ?>
</label>
<?= $form->field($minimalPasswordLength, 'value')
    ->textInput([
        'label' => Yii::t('backend', 'Минимальная длина пароля'),
        'name' => 'minimalPasswordLength',
        'type' => 'number',
    ]); ?>

<?= $form->field($passwordMustContainCapitalLetters, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Пароль должен содержать заглавные буквы'),
        'name' => 'passwordMustContainCapitalLetters'
    ]); ?>

<?= $form->field($passwordMustContainNumbers, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Пароль должен содержать цифры'),
        'name' => 'passwordMustContainNumbers'
    ]); ?>

<?= $form->field($passwordMustContainSpecialCharacters, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Пароль должен содержать специальные символы'),
        'name' => 'passwordMustContainSpecialCharacters'
    ]); ?>

<?= $form->field($confirmEmail, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Требовать подтверждение email при регистрации'),
        'name' => 'confirmEmail',
        'id' => 'confirm-email'
    ]); ?>
<span class="span-hint">
    <?php echo Yii::t('backend', '(При включенной опции "Требовать подтверждение email при регистрации", поступающие, успешно прошедшие регистрацию на портал, будут обязаны подтвердить свой email с помощью кода или ссылки, которые прейдут им на почтовый адрес, указанный при регистрации)') ?>
</span>

<label for="token-ttl">
    <?php echo Yii::t('backend', 'Время действия ссылки и кода для подтверждения email (минуты).') ?>
</label>
<?= $form->field($confirmEmailTokenTTL, 'value')
    ->textInput([
        'type' => 'number',
        'name' => 'confirmEmailTokenTTL',
        'id' => 'token-ttl'
    ]) ?>

<?php echo $form->field($allowRememberMe, 'value')
    ->checkbox([
        'label' => Yii::t('backend', 'Разрешить использовать отметку "Запомнить меня" при авторизации'),
        'name' => 'allow_remember_me',
        'id' => 'allow_remember_me'
    ]) ?>

<div id="remember_me_duration_container" style="display: <?php echo $allowRememberMe->value ? 'block' : 'none'; ?>">
    <label for="allow_remember_me">
        <?php echo Yii::t('backend', 'Срок, на который будет сохранена авторизация пользователя при выбранной отметке «Запомнить меня»'); ?>
    </label>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-4">
            <?php $itemsDuration = [
                Time::SECONDS_IN_AN_HOUR => '1 час',
                Time::SECONDS_IN_A_DAY => '1 день',
                Time::SECONDS_IN_A_WEEK => '1 неделя',
                Time::SECONDS_IN_A_MONTH => '1 месяц',
            ];
            ?>
            <div class="form-group">
                <?php echo Html::dropDownList('identity_cookie_duration_select', $identityCookieDuration->value, $itemsDuration, [
                    'prompt' => 'Указанное количество секунд',
                    'id' => 'identity_cookie_duration_select',
                    'class' => 'form-control'
                ]) ?>
            </div>
            <div id="identity_cookie_duration_block" style="display: <?php echo !in_array($identityCookieDuration->value, array_keys($itemsDuration)) ? 'block' : 'none'; ?>">
                <?= $form->field($identityCookieDuration, 'value')
                    ->textInput([
                        'type' => 'number',
                        'name' => 'identity_cookie_duration',
                        'id' => 'identity_cookie_duration',
                        'min' => 0,
                        'step' => 1
                    ]) ?>
            </div>
        </div>
    </div>
</div>

<?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary float-right']); ?>

<?php ActiveForm::end();
