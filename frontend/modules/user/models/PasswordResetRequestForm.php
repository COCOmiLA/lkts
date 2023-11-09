<?php

namespace frontend\modules\user\models;

use common\commands\command\SendEmailCommand;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\Url;




class PasswordResetRequestForm extends Model
{
    public $email;

    


    public function rules()
    {
        return [
            [
                'email',
                'filter',
                'filter' => 'trim'
            ],
            [
                'email',
                'required'
            ],
            [
                'email',
                'email'
            ],
            [
                'email', 'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t(
                    'sign-in/request-password-reset/form',
                    'Подсказка с ошибкой поля email-а на форме восстановления пароля: `Пользователя с таким "{email}" не найдено. <a href="{url}">Восстановить доступ.</a>`',
                    [
                        'url' => Url::to('/user/sign-in/abiturient-access'),
                        'email' => Yii::t(
                            'sign-in/request-password-reset/form',
                            'Заголовок поля email-а на форме восстановления пароля: `E-mail`'
                        )
                    ]
                ),
            ],
        ];
    }

    




    public function sendEmail()
    {
        

        try {
            if ($user = User::findOne(['status' => User::STATUS_ACTIVE, 'email' => $this->email])) {
                $user->generatePasswordResetToken();
                if ($user->save()) {
                    return Yii::$app->commandBus->handle(new SendEmailCommand([
                        'from' => [Yii::$app->params['adminEmail'] => Yii::$app->name],
                        'to' => $this->email,
                        'subject' => Yii::t(
                            'sign-in/request-password-reset/email',
                            'Заголовок письма для восстановления пароля: `Сброс пароля для {name}`',
                            ['name' => Yii::$app->name]
                        ),
                        'view' => 'passwordResetToken',
                        'params' => ['user' => $user]
                    ]));
                }
            }
        } catch (\Throwable $e) {
            Yii::error("Ошибка отправки почты: ({$e->getMessage()}) ");
        }

        return false;
    }

    public function attributeLabels()
    {
        return ['email' => Yii::t(
            'sign-in/request-password-reset/form',
            'Заголовок поля email-а на форме восстановления пароля: `E-mail`'
        )];
    }
}
