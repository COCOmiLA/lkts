<?php
namespace frontend\modules\user\models;

use common\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;




class ResetPasswordForm extends Model
{
    public $password;

    


    private $user;

    






    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }
        $this->user = User::findByPasswordResetToken($token);
        if (!$this->user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    


    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    




    public function resetPassword()
    {
        $user = $this->user;
        $user->password = $this->password;
        $user->removePasswordResetToken();

        return $user->save();
    }

    public function attributeLabels()
    {
        return [
            'password'=>Yii::t('frontend', 'Пароль')
        ];
    }
}
