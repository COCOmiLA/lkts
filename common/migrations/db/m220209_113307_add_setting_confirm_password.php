<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220209_113307_add_setting_confirm_password extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert(
            '{{%auth_settings}}',
            [
                'value' => 1,
                'name' => 'confirm_password',
            ]
        );
    }

    public function safeDown()
    {
        $this->delete('{{%auth_settings}}', ['name' => 'confirm_password']);
    }
}
