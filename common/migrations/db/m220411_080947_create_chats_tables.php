<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220411_080947_create_chats_tables extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->createTable('{{%chat}}', [
            'id' => $this->primaryKey()->unsigned(),

            'type' => $this->integer()->unsigned()->defaultValue(1),
            'status' => $this->integer()->unsigned()->defaultValue(1),

            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ]);
        


        
        $this->createTable('{{%chat_user}}', [
            'id' => $this->primaryKey()->unsigned(),

            'user_id' => $this->integer()->notNull(),
            'chat_id' => $this->integer()->unsigned()->defaultValue(null),
            'first_name' => $this->string()->defaultValue(''),
            'second_name' => $this->string()->defaultValue(''),
            'last_name' => $this->string()->defaultValue(''),
            'nickname' => $this->string()->defaultValue(''),
            'email' => $this->string()->defaultValue(''),
            'online_status' => $this->boolean()->defaultValue(false),
            'avatar_id' => $this->integer()->unsigned(),
            'status' => $this->tinyInteger()->unsigned()->defaultValue(0),
            'archive' => $this->boolean()->defaultValue(false),

            'created_at' => $this->bigInteger()->unsigned(),
            'updated_at' => $this->bigInteger()->unsigned(),
        ]);

        $this->createIndex(
            'IDX-chat_user-chat_id-user_id',
            '{{%chat_user}}',
            ['chat_id', 'user_id'],
            true
        );

        $this->addForeignKey(
            'FK-chat_user-chat_id',
            '{{%chat_user}}',
            'chat_id',
            '{{%chat}}',
            'id',
        );

        $this->addForeignKey(
            'FK-chat_user-user_id',
            '{{%chat_user}}',
            'user_id',
            '{{%user}}',
            'id',
        );
        


        
        $options = null;
        if (Yii::$app->db->driverName === 'mysql') {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%chat_message}}', [
            'id' => $this->primaryKey()->unsigned(),

            'chat_id' => $this->integer()->unsigned()->notNull(),
            'author_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->integer()->unsigned()->defaultValue(1),
            'mark_is_not_read' => $this->boolean()->defaultValue(false),
            'message' => $this->binary(),

            'created_at' => $this->bigInteger()->unsigned(),
            'updated_at' => $this->bigInteger()->unsigned(),
        ], $options);

        $this->createIndex(
            'IDX-chat_message-chat_id-author_id',
            '{{%chat_message}}',
            ['chat_id', 'author_id']
        );

        $this->addForeignKey(
            'FK-chat_message-chat_id',
            '{{%chat_message}}',
            'chat_id',
            '{{%chat}}',
            'id',
        );

        $this->addForeignKey(
            'FK-chat_message-user_id',
            '{{%chat_message}}',
            'author_id',
            '{{%chat_user}}',
            'id',
        );
        


        
        $this->createTable('{{%chat_file}}', [
            'id' => $this->primaryKey()->unsigned(),

            'chat_id' => $this->integer()->unsigned()->notNull(),
            'author_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->integer()->unsigned()->defaultValue(1),
            'mark_is_not_read' => $this->boolean()->defaultValue(false),
            'file_id' => $this->integer()->defaultValue(null),

            'created_at' => $this->bigInteger()->unsigned(),
            'updated_at' => $this->bigInteger()->unsigned(),
        ], $options);

        $this->createIndex(
            'IDX-chat_file-chat_id-author_id',
            '{{%chat_file}}',
            ['chat_id', 'author_id']
        );

        $this->createIndex(
            'IDX-chat_file-file_id',
            '{{%chat_file}}',
            ['file_id']
        );

        $this->addForeignKey(
            'FK-chat_file-chat_id',
            '{{%chat_file}}',
            'chat_id',
            '{{%chat}}',
            'id',
        );

        $this->addForeignKey(
            'FK-chat_file-file_id',
            '{{%chat_file}}',
            'file_id',
            '{{%files}}',
            'id',
        );

        $this->addForeignKey(
            'FK-chat_file-user_id',
            '{{%chat_file}}',
            'author_id',
            '{{%chat_user}}',
            'id',
        );
        


        
        $this->createTable('{{%chat_history}}', [
            'id' => $this->primaryKey()->unsigned(),

            'chat_id' => $this->integer()->unsigned()->notNull(),
            'event' => $this->integer()->unsigned()->defaultValue(1),
            'message_id' => $this->integer()->unsigned()->defaultValue(null),
            'file_id' => $this->integer()->unsigned()->defaultValue(null),

            'created_at' => $this->bigInteger()->unsigned(),
            'updated_at' => $this->bigInteger()->unsigned(),
        ]);

        $this->createIndex(
            'IDX-chat_history-chat_id',
            '{{%chat_history}}',
            ['chat_id']
        );

        $this->createIndex(
            'IDX-chat_history-chat_id-message_id',
            '{{%chat_history}}',
            ['chat_id', 'message_id']
        );

        $this->createIndex(
            'IDX-chat_history-chat_id-file_id',
            '{{%chat_history}}',
            ['chat_id', 'file_id']
        );

        $this->addForeignKey(
            'FK-chat_history-chat_id',
            '{{%chat_history}}',
            'chat_id',
            '{{%chat}}',
            'id',
        );

        $this->addForeignKey(
            'FK-chat_history-message_id',
            '{{%chat_history}}',
            'message_id',
            '{{%chat_message}}',
            'id',
        );

        $this->addForeignKey(
            'FK-chat_history-file_id',
            '{{%chat_history}}',
            'file_id',
            '{{%chat_file}}',
            'id',
        );
        

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK-chat_file-chat_id', '{{%chat_file}}');
        $this->dropForeignKey('FK-chat_file-file_id', '{{%chat_file}}');
        $this->dropForeignKey('FK-chat_file-user_id', '{{%chat_file}}');
        $this->dropForeignKey('FK-chat_user-chat_id', '{{%chat_user}}');
        $this->dropForeignKey('FK-chat_user-user_id', '{{%chat_user}}');
        $this->dropForeignKey('FK-chat_history-chat_id', '{{%chat_history}}');
        $this->dropForeignKey('FK-chat_history-file_id', '{{%chat_history}}');
        $this->dropForeignKey('FK-chat_message-chat_id', '{{%chat_message}}');
        $this->dropForeignKey('FK-chat_message-user_id', '{{%chat_message}}');
        $this->dropForeignKey('FK-chat_history-message_id', '{{%chat_history}}');

        $this->dropIndex('IDX-chat_file-file_id', '{{%chat_file}}');
        $this->dropIndex('IDX-chat_history-chat_id', '{{%chat_history}}');
        $this->dropIndex('IDX-chat_user-chat_id-user_id', '{{%chat_user}}');
        $this->dropIndex('IDX-chat_file-chat_id-author_id', '{{%chat_file}}');
        $this->dropIndex('IDX-chat_history-chat_id-file_id', '{{%chat_history}}');
        $this->dropIndex('IDX-chat_message-chat_id-author_id', '{{%chat_message}}');
        $this->dropIndex('IDX-chat_history-chat_id-message_id', '{{%chat_history}}');

        $this->dropTable('{{%chat}}');
        $this->dropTable('{{%chat_file}}');
        $this->dropTable('{{%chat_user}}');
        $this->dropTable('{{%chat_history}}');
        $this->dropTable('{{%chat_message}}');

        Yii::$app->db->schema->refresh();
    }
}
