<?php

namespace backend\models;

use cheatsheet\Time;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;




class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $user = false;
    
    public function __construct($config = [])
    {
        parent::__construct($config);
        if (Yii::$app->configurationManager->getAllowRememberMe()) {
            $this->rememberMe = false;
        }
    }

    


    public function rules()
    {
        return [
            
            [['username', 'password'], 'required'],
            
            ['rememberMe', 'boolean'],
            
            ['password', 'validatePassword'],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'username' => Yii::t('backend', 'Имя пользователя'),
            'password' => Yii::t('backend', 'Пароль'),
            'rememberMe' => Yii::t('backend', 'Запомнить меня')
        ];
    }

    



    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', Yii::t('backend', 'Неправильный логин или пароль.'));
            }
        }
    }

    




    public function login()
    {
        if (!$this->validate()) {
            return false;
        }
        
        $rememberMeDuration = Yii::$app->configurationManager->getIdentityCookieDuration();
        
        if (Yii::$app->user->login($this->getUser(), $this->rememberMe ? $rememberMeDuration : 0)) {
            if (!Yii::$app->user->can('loginToBackend')) {
                Yii::$app->user->logout();
                throw new ForbiddenHttpException();
            }
            return true;
        }

        return false;
    }

    




    public function getUser()
    {
        if ($this->user === false) {
            $this->user = User::findActive()
                ->andWhere(['or', ['username' => $this->username], ['email' => $this->username]])
                ->limit(1)
                ->one();
        }

        return $this->user;
    }
}
