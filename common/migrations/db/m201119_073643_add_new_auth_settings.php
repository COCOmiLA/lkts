<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\AuthSetting;




class m201119_073643_add_new_auth_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $confirmEmailAuthSetting = new AuthSetting();
        $confirmEmailAuthSetting->name = "confirm_email";
        $confirmEmailAuthSetting->value = "0";
        $confirmEmailAuthSetting->save();

        $confirmTokenTTLAuthSetting = new AuthSetting();
        $confirmTokenTTLAuthSetting->name = "confirm_email_token_ttl";
        $confirmTokenTTLAuthSetting->value = "10";
        $confirmTokenTTLAuthSetting->save();
    }

    


    public function safeDown()
    {
        $confirmEmailAuthSetting = AuthSetting::findOne([
            'name' =>  "confirm_email"
        ]);
        if($confirmEmailAuthSetting !== null) {
            $confirmEmailAuthSetting->delete();
        }

        $confirmTokenTTLAuthSetting = AuthSetting::findOne([
            'name' =>  "confirm_email_token_ttl"
        ]);
        if($confirmTokenTTLAuthSetting !== null) {
            $confirmTokenTTLAuthSetting->delete();
        }
    }

    













}
