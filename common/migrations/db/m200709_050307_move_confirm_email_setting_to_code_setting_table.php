<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200709_050307_move_confirm_email_setting_to_code_setting_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%code_settings}}', [
            'name' => 'confirm-email',
            'value' => '1',
            'description' => 'Требовать повторного ввода email',
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%code_settings}}', [
            'name' => 'confirm-email',
        ]);
    }
}
