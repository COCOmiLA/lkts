<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220511_133834_create_manager_allow_chat_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%manager_allow_chat}}', [
            'id' => $this->primaryKey(),

            'manager_id' => $this->integer()->notNull(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-manager_allow_chat-manager_id',
            '{{%manager_allow_chat}}',
            'manager_id',
            '{{%user}}',
            'id',
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk-manager_allow_chat-manager_id', '{{%manager_allow_chat}}');

        $this->dropTable('{{%manager_allow_chat}}');
    }
}
