<?php


namespace common\components;


use common\models\User;
use common\models\UserRegistrationConfirmToken;

class UserEmailConfirmTokenManager
{
    





    public static function createUserEmailConfirmToken(User $user): UserRegistrationConfirmToken {
        $token = new UserRegistrationConfirmToken();
        
        self::archiveAllTokensByUser($user);
        $token->initializeToken($user);
        $token->save();
        return $token;
    }

    



    public static function archiveAllTokensByUser(User $user) {
        UserRegistrationConfirmToken::updateAll([
            'status' => UserRegistrationConfirmToken::STATUS_DEPRECATED,
        ], [
            'user_id' => $user->id,
            'status' => UserRegistrationConfirmToken::STATUS_UNTOUCHED
        ]);
    }
}