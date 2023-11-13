<?php

namespace frontend\models;

use filsh\yii2\oauth2server\models\OauthAuthorizationCodes;

class AuthorizationCodeStorage extends OauthAuthorizationCodes implements \OAuth2\Storage\AuthorizationCodeInterface
{
    
    public function getAuthorizationCode($code)
    {
        $authorization_code = OauthAuthorizationCodes::findOne(['authorization_code' => $code]);
        
        if($authorization_code == null) return null;
        
            return [
                "client_id"    => $authorization_code->client_id,      
                "user_id"      => $authorization_code->user_id,        
                "expires"      => strtotime($authorization_code->expires),        
                "redirect_uri" => $authorization_code->redirect_uri,   
                "scope"        => $authorization_code->scope,          
            ];
    }
    
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        $expire = date("Y-m-d H:i:s", strtotime('+4 hour'));
        $authorization_code = new OauthAuthorizationCodes();
        $authorization_code->client_id = $client_id;
        $authorization_code->authorization_code = $code;
        $authorization_code->user_id = $user_id;
        $authorization_code->expires = $expire;
        $authorization_code->redirect_uri = $redirect_uri;
        $authorization_code->scope = $scope;
        
        $authorization_code->save();
    }
    
    public function expireAuthorizationCode($code)
    {
        $authorization_code = OauthAuthorizationCodes::findOne(['authorization_code' => $code]);
        $authorization_code->delete();
    }
}
