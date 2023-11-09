<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220512_105916_create_chat_settings_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%chat_settings}}', [
            'id' => $this->primaryKey(),

            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string(1000)->notNull(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->insert('{{%chat_settings}}', [
            'name' => 'request_interval',
            'description' => 'Периодичность опроса (сек)',
            'value' => '5',

            'created_at' => time(),
            'updated_at' => time(),
        ]);

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%chat_settings}}');

        Yii::$app->db->schema->refresh();
    }
}
