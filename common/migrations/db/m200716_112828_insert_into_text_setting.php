<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200716_112828_insert_into_text_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('text_settings', [
            'name' => 'reset_link_text',
            'description' => 'Тексты ссылки на сброс пароля на странице авторизации',
            'value' => 'Забыли пароль? Перейдите по ссылке',
            'order' => 0,
            'category' => 0,
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('text_settings', ['name' => 'reset_link_text']);
    }
}
