<?php
namespace common\models\repositories;

use common\models\User;
use common\models\UserRegistrationConfirmToken;

class UserRegistrationConfirmTokenRepository
{
    




    public static function findActiveTokenByHashAndUser($hash, User $user) {
        return UserRegistrationConfirmToken::findOne([
            'user_id' => $user->id,
            'confirm_token' => $hash,
            'status' => UserRegistrationConfirmToken::STATUS_UNTOUCHED
        ]);
    }
}