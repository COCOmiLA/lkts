<?php

namespace frontend\modules\user\models;

use cheatsheet\Time;
use common\models\Recaptcha;
use common\models\User;
use Yii;
use yii\base\Model;




class AbiturientLoginForm extends Model
{
    public $identity;
    public $password;
    public $rememberMe = true;
    


    private $user = false;

    public $reCaptcha;

    


    public function rules()
    {
        $rules = [
            
            [['identity', 'password'], 'required'],
            
            ['rememberMe', 'boolean'],
            
            ['password', 'validatePassword'],
            
        ];

        $validator = Recaptcha::getValidationArrayByName('login');
        if (!empty($validator)) {
            $rules[] = $validator;
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'identity' => Yii::t('frontend', 'Имя пользователя'),
            'password' => Yii::t('frontend', 'Пароль'),
            'rememberMe' => Yii::t('frontend', 'Запомнить меня'),
        ];
    }


    



    public function validatePassword()
    {
        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', Yii::t('frontend', 'Неправильный логин или пароль.'));
        }
    }

    




    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $status = $user->testConnection();
            if ($status && Yii::$app->user->login($this->getUser(), $this->rememberMe ? Time::SECONDS_IN_A_MONTH : 0)) {
                return true;
            }
        }
        return false;
    }

    




    public function getUser()
    {
        if ($this->user === false) {
            $this->user = User::findActive()->andWhere(['or', ['username' => $this->identity], ['email' => $this->identity]])->one();
        }

        return $this->user;
    }
}
