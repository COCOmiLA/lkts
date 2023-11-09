<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230404_092346_change_foreign_key_for_chat extends MigrationWithDefaultOptions
{
    private const DROP_FK_LIST = [
        'FK-chat_file-chat_id' => '{{%chat_file}}',
        'FK-chat_file-user_id' => '{{%chat_file}}',
        'FK-chat_user-chat_id' => '{{%chat_user}}',
        'FK-chat_history-chat_id' => '{{%chat_history}}',
        'FK-chat_history-file_id' => '{{%chat_history}}',
        'FK-chat_message-chat_id' => '{{%chat_message}}',
        'FK-chat_message-user_id' => '{{%chat_message}}',
        'FK-chat_history-message_id' => '{{%chat_history}}',
    ];

    


    public function safeUp()
    {
        $this->alterColumn('{{%chat_message}}', 'chat_id', $this->integer()->unsigned()->defaultValue(null));
        $this->alterColumn('{{%chat_message}}', 'author_id', $this->integer()->unsigned()->defaultValue(null));

        $this->alterColumn('{{%chat_file}}', 'chat_id', $this->integer()->unsigned()->defaultValue(null));
        $this->alterColumn('{{%chat_file}}', 'author_id', $this->integer()->unsigned()->defaultValue(null));

        $this->alterColumn('{{%chat_history}}', 'chat_id', $this->integer()->unsigned()->defaultValue(null));

        $this->deleteFk();

        Yii::$app->db->schema->refresh();

        $this->addForeignKey(
            'FK-chat_user-chat_id',
            '{{%chat_user}}',
            'chat_id',
            '{{%chat}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_message-chat_id',
            '{{%chat_message}}',
            'chat_id',
            '{{%chat}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_message-user_id',
            '{{%chat_message}}',
            'author_id',
            '{{%chat_user}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_file-chat_id',
            '{{%chat_file}}',
            'chat_id',
            '{{%chat}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_file-user_id',
            '{{%chat_file}}',
            'author_id',
            '{{%chat_user}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_history-chat_id',
            '{{%chat_history}}',
            'chat_id',
            '{{%chat}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_history-message_id',
            '{{%chat_history}}',
            'message_id',
            '{{%chat_message}}',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'FK-chat_history-file_id',
            '{{%chat_history}}',
            'file_id',
            '{{%chat_file}}',
            'id',
            'SET NULL'
        );

        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%chat_message}}', 'chat_id', $this->integer()->unsigned()->defaultValue(null));
        $this->alterColumn('{{%chat_message}}', 'author_id', $this->integer()->unsigned()->defaultValue(null));

        $this->alterColumn('{{%chat_file}}', 'chat_id', $this->integer()->unsigned()->defaultValue(null));
        $this->alterColumn('{{%chat_file}}', 'author_id', $this->integer()->unsigned()->defaultValue(null));

        $this->alterColumn('{{%chat_history}}', 'chat_id', $this->integer()->unsigned()->defaultValue(null));

        $this->deleteFk();

        Yii::$app->db->schema->refresh();

        $this->addForeignKey(
            'FK-chat_user-chat_id',
            '{{%chat_user}}',
            'chat_id',
            '{{%chat}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_message-chat_id',
            '{{%chat_message}}',
            'chat_id',
            '{{%chat}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_message-user_id',
            '{{%chat_message}}',
            'author_id',
            '{{%chat_user}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_file-chat_id',
            '{{%chat_file}}',
            'chat_id',
            '{{%chat}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_file-user_id',
            '{{%chat_file}}',
            'author_id',
            '{{%chat_user}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_history-chat_id',
            '{{%chat_history}}',
            'chat_id',
            '{{%chat}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_history-message_id',
            '{{%chat_history}}',
            'message_id',
            '{{%chat_message}}',
            'id'
        );
        $this->addForeignKey(
            'FK-chat_history-file_id',
            '{{%chat_history}}',
            'file_id',
            '{{%chat_file}}',
            'id'
        );

        Yii::$app->db->schema->refresh();
    }

    


    private function deleteFk(): void
    {
        Yii::$app->db->schema->refresh();

        foreach (self::DROP_FK_LIST as $fkName => $tableName) {
            $this->dropForeignKey($fkName, $tableName);
        }
    }
}
