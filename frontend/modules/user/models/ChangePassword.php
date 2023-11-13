<?php






namespace frontend\modules\user\models;

use Yii;
use yii\base\Model;

class ChangePassword extends Model {
    public $password;
    public $new_password;
    public $repeat_new_password;

    public function rules() {
        return [
            [['password', 'new_password', 'repeat_new_password'], 'required'],
            
        ];
    }

    public function attributeLabels() {
        return [
            'password' => Yii::t('frontend', 'Старый пароль'),
            'new_password' => Yii::t('frontend', 'Новый пароль'),
            'repeat_new_password' => Yii::t('frontend', 'Подтверждение нового пароля'),
        ];
    }
}
