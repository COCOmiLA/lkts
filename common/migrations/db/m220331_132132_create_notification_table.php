<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220331_132132_create_notification_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%notification_content}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'body' => $this->text()
        ]);
        
        
        $this->createTable('{{%notification}}', [
            'id' => $this->primaryKey(),
            'sender_id' => $this->integer(),
            'receiver_id' => $this->integer()->notNull(),
            'notification_content_id' => $this->integer()->notNull(),
            'category' => $this->string(),
            'shown' => $this->boolean()->defaultValue(false),
            'read_at' => $this->bigInteger(),
            'created_at' => $this->bigInteger(),
            'updated_at' => $this->bigInteger()
        ]);
        $this->addForeignKey('{{%fk-notification_content-notification_content_id}}', '{{%notification}}', 'notification_content_id', '{{%notification_content}}', 'id');
        $this->createIndex('{{%idx-notification_content-notification_content_id}}', '{{%notification}}', 'notification_content_id');
        $this->addForeignKey('{{%fk-notification_content-sender_id}}', '{{%notification}}', 'sender_id', '{{%user}}', 'id');
        $this->createIndex('{{%idx-notification_content-sender_id}}', '{{%notification}}', 'sender_id');
        $this->addForeignKey('{{%fk-notification_content-receiver_id}}', '{{%notification}}', 'receiver_id', '{{%user}}', 'id');
        $this->createIndex('{{%idx-notification_content-receiver_id}}', '{{%notification}}', 'receiver_id');
        
        
        $this->createTable('{{%notification_attachment}}', [
            'id' => $this->primaryKey(),
            'notification_id' => $this->integer()->notNull(),
            'created_at' => $this->bigInteger(),
            'updated_at' => $this->bigInteger()
        ]);
        $this->addForeignKey('{{%fk-notification_attachment-notification_id}}', '{{%notification_attachment}}', 'notification_id', '{{%notification}}', 'id');
        $this->createIndex('{{%idx-notification_attachment-notification_id}}', '{{%notification_attachment}}', 'notification_id');
        
        
        $this->createTable('{{%notification_attachment_files}}', [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'notification_attachment_id' => $this->integer()->notNull()
        ]);
        $this->addForeignKey('{{%fk-notification_attachment_files-notification_attachment_id}}', '{{%notification_attachment_files}}', 'notification_attachment_id', '{{%notification_attachment}}', 'id');
        $this->createIndex('{{%idx-notification_attachment_files-notification_attachment_id}}', '{{%notification_attachment_files}}', 'notification_attachment_id');
        $this->addForeignKey('{{%fk-notification_attachment_files-file_id}}', '{{%notification_attachment_files}}', 'file_id', '{{%files}}', 'id');
        $this->createIndex('{{%idx-notification_attachment_files-file_id}}', '{{%notification_attachment_files}}', 'file_id');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-notification_attachment_files-file_id}}', '{{%notification_attachment_files}}');
        $this->dropIndex('{{%idx-notification_attachment_files-file_id}}', '{{%notification_attachment_files}}');
        $this->dropForeignKey('{{%fk-notification_attachment_files-notification_attachment_id}}', '{{%notification_attachment_files}}');
        $this->dropIndex('{{%idx-notification_attachment_files-notification_attachment_id}}', '{{%notification_attachment_files}}');
        $this->dropTable('{{%notification_attachment_files}}');
        
        
        $this->dropForeignKey('{{%fk-notification_attachment-notification_id}}', '{{%notification_attachment}}');
        $this->dropIndex('{{%idx-notification_attachment-notification_id}}', '{{%notification_attachment}}');
        $this->dropTable('{{%notification_attachment}}');
        
        
        $this->dropForeignKey('{{%fk-notification_content-receiver_id}}', '{{%notification}}');
        $this->dropIndex('{{%idx-notification_content-receiver_id}}', '{{%notification}}');
        $this->dropForeignKey('{{%fk-notification_content-sender_id}}', '{{%notification}}');
        $this->dropIndex('{{%idx-notification_content-sender_id}}', '{{%notification}}');
        $this->dropForeignKey('{{%fk-notification_content-notification_content_id}}', '{{%notification}}');
        $this->dropIndex('{{%idx-notification_content-notification_content_id}}', '{{%notification}}');
        $this->dropTable('{{%notification}}');
        
        $this->dropTable('{{%notification_content}}');
    }
}
