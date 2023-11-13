<?php

namespace frontend\modules\user\models;

use common\components\UserEmailConfirmTokenManager;
use common\models\User;
use common\models\UserRegistrationConfirmToken;
use Exception;
use frontend\modules\user\exceptions\userEmailConfirmExceptions\EmailTokenExpiredException;
use frontend\modules\user\exceptions\userEmailConfirmExceptions\EmailTokenNotFoundException;
use frontend\modules\user\exceptions\userEmailConfirmExceptions\EmailTokenValidationException;
use Yii;
use yii\base\Model;
use yii\base\UserException;




class EmailCodeConfirmForm extends Model
{
    


    public $code;
    


    public $user;

    public function __construct(User $user, $config = [])
    {
        parent::__construct($config);
        $this->user = $user;
    }

    


    public function rules()
    {

        return [
            ['code', 'string', 'length' => UserRegistrationConfirmToken::CODE_LENGTH],
            ['code', 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => Yii::t(
                'sign-in/confirm-email/form',
                'Подпись для поля "code"; страницы подтверждения почты: `Код подтверждения`'
            )
        ];
    }

    










    public function handleCode($time): bool
    {
        $token = $this->user->userRegistrationConfirmToken;
        if ($token === null) {
            throw new EmailTokenNotFoundException();
        }
        if ($token->isExpired($time)) {
            throw new EmailTokenExpiredException();
        }

        if ($token->confirm_code !== $this->code) {
            throw new EmailTokenValidationException();
        }

        $transaction = Yii::$app->db->beginTransaction();
        if ($transaction === null) {
            throw new UserException('Невозможно создать транзакцию');
        }
        UserEmailConfirmTokenManager::archiveAllTokensByUser($this->user);
        $this->user->addUserRegistrationConfirm();
        $transaction->commit();

        return true;
    }
}
