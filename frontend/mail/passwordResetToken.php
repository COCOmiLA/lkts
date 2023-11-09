<?php

use common\models\User;
use yii\helpers\Html;
use yii\web\View;






$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/user/sign-in/reset-password', 'token' => $user->password_reset_token]);

Yii::t(
    'sign-in/request-password-reset/email',
    'Текст письма для восстановления пароля: `Здравствуйте {username}, для сброса пароля перейдите по ссылке:`',
    ['username' => Html::encode($user->username)]
)

?>

<?= Html::a(Html::encode($resetLink), $resetLink);