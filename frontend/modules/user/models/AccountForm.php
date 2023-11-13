<?php
namespace frontend\modules\user\models;

use Yii;
use yii\base\Model;




class AccountForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_confirm;

    private $user;

    public function setUser($user)
    {
        $this->user = $user;
        $this->email = $user->email;
        $this->username = $user->username;
    }

    


    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique',
             'targetClass'=>'\common\models\User',
             'message' => Yii::t('frontend', 'Это имя пользователя уже занято'),
             'filter' => function ($query) {
                 $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
             }
            ],
            ['username', 'string', 'min' => 1, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique',
             'targetClass'=>'\common\models\User',
             'message' => Yii::t('frontend', 'Этот e-mail уже занят.'),
             'filter' => function ($query) {
                 $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
             }
            ],
            ['password', 'string'],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'username'=>Yii::t('frontend', 'Имя пользователя'),
            'email'=>Yii::t('frontend', 'Email'),
            'password'=>Yii::t('frontend', 'Пароль'),
            'password_confirm'=>Yii::t('frontend', 'Подтвердите пароль')
        ];
    }

    public function save()
    {
        $this->user->username = $this->username;
        $this->user->email = $this->email;
        if ($this->password) {
            $this->user->setPassword($this->password);
        }
        return $this->user->save();
    }
}
