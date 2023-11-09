<?php
namespace backend\models;

use Yii;
use yii\base\Model;




class AccountForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_confirm;

    


    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique',
             'targetClass'=>'\common\models\User',
             'message' => Yii::t('backend', 'Это имя пользователя уже занято'),
             'filter' => function ($query) {
                 $query->andWhere(['not', ['id'=>Yii::$app->user->id]]);
             }
            ],
            ['username', 'string', 'min' => 1, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique',
             'targetClass'=>'\common\models\User',
             'message' => Yii::t('backend', 'Этот e-mail уже занят.'),
             'filter' => function ($query) {
                 $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
             }
            ],
            ['password', 'string'],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password']
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'username' => Yii::t('backend', 'Имя пользователя'),
            'email' => Yii::t('backend', 'Email'),
            'password' => Yii::t('backend', 'Пароль'),
            'password_confirm' => Yii::t('backend', 'Подтверждение пароля')
        ];
    }
}
