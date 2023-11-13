<?php

use cheatsheet\Time;
use common\components\Migration\MigrationWithDefaultOptions;




class m220901_130949_add_remember_me_auth_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%auth_settings}}', [
            'name' => 'allow_remember_me',
            'value' => 1
        ]);
        
        $this->insert('{{%auth_settings}}', [
            'name' => 'identity_cookie_duration',
            'value' => Time::SECONDS_IN_A_MONTH
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%auth_settings}}', [
            'name' => [
                'allow_remember_me',
                'identity_cookie_duration'
            ]
        ]);
    }
}
