<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220829_063359_chat_killswitch extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->insert('{{%chat_settings}}', [
            'name' => 'enable_chat',
            'description' => 'Включить чат с приёмной комиссией',
            'value' => 1,

            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    


    public function safeDown()
    {
        $this->delete('{{%chat_settings}}', ['name' => 'enable_chat']);
    }
}
