<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\base\Model;









class LoginForm extends Model
{
    


    public $username;

    


    public $password;

    


    public $rememberMe = false;

    


    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['username', 'string', 'min' => '3'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    



    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->user;
            if (empty($user) || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    



    public function login()
    {
        if ($this->validate()) {
            return Podium::getInstance()->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    private $_user = false;

    



    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByKeyfield($this->username);
        }
        return $this->_user;
    }
}
