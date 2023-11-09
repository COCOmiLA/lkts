<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220201_060103_drop_text_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->delete(
            '{{%text_settings}}',
            ['name' => 'sandbox_modified']
        );
    }

    


    public function safeDown()
    {
        $this->insert('{{%text_settings}}', [
            'name' => 'sandbox_modified',
            'description' => 'Сообщение модератору о том, что заявление было изменено пользователем',
            'value' => 'Внимание! Заявление могло быть изменено пользователем. Пожалуйста, обновите страницу перед проверкой',
        ]);
    }
}
