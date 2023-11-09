<?php

namespace frontend\models;

use filsh\yii2\oauth2server\models\OauthClients;

class ClientCredentialsStorage implements \OAuth2\Storage\ClientCredentialsInterface
{
    public function checkClientCredentials($client_id, $client_secret = null){
        
        $client = OauthClients::findOne(['client_id' => $client_id, 'client_secret' => $client_secret]);
        if(isset($client) && $client != null){
            return true;
        }
        else{
            return false;
        }
    }
    
    public function isPublicClient($client_id){
        return false;
    }
    
    public function getClientDetails($client_id){
        $client = OauthClients::findOne(['client_id' => $client_id]);
        if(isset($client) && $client != null){
            return [
                    "redirect_uri" => $client->redirect_uri,    
                    "client_id"    => $client->client_,        
                    "grant_types"  => $client->client_secret,                 
                   ];
        }
        else{
            return [];
        }
    }
    
    public function getClientScope($client_id){
        return "";
    }
    
    public function checkRestrictedGrantType($client_id, $grant_type){
        return true;
    }
}

