<?php

namespace frontend\modules\user\models;

use common\models\Recaptcha;
use common\models\User;
use Yii;
use yii\base\Model;




class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    public $reCaptcha;

    


    public function rules()
    {
        $rules = [
            [
                'username',
                'filter',
                'filter' => 'trim'
            ],
            [
                'username',
                'required'
            ],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('frontend', 'Это имя пользователя уже занято')
            ],
            [
                'username',
                'string',
                'min' => 2,
                'max' => 255
            ],

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
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('frontend', 'Этот адрес электронной почты уже занят')
            ],

            [
                'password',
                'required'
            ],
            [
                'password',
                'string',
                'min' => 6
            ],

        ];

        $validator = Recaptcha::getValidationArrayByName('signup');
        if (!empty($validator)) {
            $rules[] = $validator;
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('frontend', 'Имя пользователя'),
            'email' => Yii::t('frontend', 'E-mail'),
            'password' => Yii::t('frontend', 'Пароль'),
        ];
    }

    




    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->save();
            $user->afterSignup();
            return $user;
        }

        return null;
    }
}
