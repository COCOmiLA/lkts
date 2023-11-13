<?php

use common\components\Migration\MigrationWithDefaultOptions;









class m201002_094951_create_user_regulation_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%user_regulation}}', [
            'id' => $this->primaryKey(),
            'owner_id' => $this->integer()->null(),
            'attachment_id' => $this->integer()->null(),
            'regulation_id' => $this->integer()->null(),
            'application_id' => $this->integer()->null(),
            'is_confirmed' => $this->boolean()->null(),
        ]);

        
        $this->createIndex(
            '{{%idx-user_regulation-owner_id}}',
            '{{%user_regulation}}',
            'owner_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user_regulation-owner_id}}',
            '{{%user_regulation}}',
            'owner_id',
            '{{%user}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-user_regulation-attachment_id}}',
            '{{%user_regulation}}',
            'attachment_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user_regulation-attachment_id}}',
            '{{%user_regulation}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-user_regulation-regulation_id}}',
            '{{%user_regulation}}',
            'regulation_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user_regulation-regulation_id}}',
            '{{%user_regulation}}',
            'regulation_id',
            '{{%regulation}}',
            'id',
            'NO ACTION'
        );
        
        $this->createIndex(
            '{{%idx-user_regulation-application_id}}',
            '{{%user_regulation}}',
            'application_id'
        );

        
        $this->addForeignKey(
            '{{%fk-user_regulation-application_id}}',
            '{{%user_regulation}}',
            'application_id',
            '{{%bachelor_application}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-user_regulation-owner_id}}',
            '{{%user_regulation}}'
        );

        
        $this->dropIndex(
            '{{%idx-user_regulation-owner_id}}',
            '{{%user_regulation}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-user_regulation-attachment_id}}',
            '{{%user_regulation}}'
        );

        
        $this->dropIndex(
            '{{%idx-user_regulation-attachment_id}}',
            '{{%user_regulation}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-user_regulation-regulation_id}}',
            '{{%user_regulation}}'
        );

        
        $this->dropIndex(
            '{{%idx-user_regulation-regulation_id}}',
            '{{%user_regulation}}'
        );

        $this->dropTable('{{%user_regulation}}');
    }
}
