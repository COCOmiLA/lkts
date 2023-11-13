<?php

namespace frontend\modules\user\models;

use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\Recaptcha;
use Yii;
use yii\base\Model;




class AbiturientRecoverForm extends Model
{
    


    public $email;
    


    public $password;
    


    public $passwordRepeat;
    


    public $user_ref;

    public $reCaptcha;

    


    public function rules()
    {
        $rules = [
            [['email', 'password', 'passwordRepeat'], 'string'],
            [['email'], 'required'],
            [['user_ref'], 'safe'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            [
                'email', 'unique',
                'targetClass' => '\common\models\User',
                'message' => Yii::t('frontend', 'Этот адрес электронной почты уже занят') . ". <a href='/user/sign-in/request-password-reset'>Восстановить пароль.</a>"
            ],
            [['password', 'passwordRepeat'], 'required', 'when' => function ($model) {
                return (!Yii::$app->configurationManager->signupEmailEnabled);
            }, 'whenClient' => "function (attribute, value) {
                    var auth = $('#vt').children('option').val();
                    if(auth != '" . (int)Yii::$app->configurationManager->signupEmailEnabled . "')
                    {
                            return true;
                    }
            }"],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'operator' => '==', 'message' => 'Введенные пароли не совпадают'],
            ['password', 'string', 'min' => 6, 'when' => function ($model) {
                return (!Yii::$app->configurationManager->signupEmailEnabled);
            }]
        ];

        $validator = Recaptcha::getValidationArrayByName('abit_access');
        if (!empty($validator)) {
            $rules[] = $validator;
        }

        return $rules;
    }

    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);
        if ($result) {
            if ($this->user_ref && is_numeric($this->user_ref)) {
                $this->user_ref = StoredUserReferenceType::findOne($this->user_ref);
            }
        }
        return $result;
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Электронная почта',
            'password' => 'Пароль',
            'passwordRepeat' => 'Повторите пароль',
        ];
    }
}
