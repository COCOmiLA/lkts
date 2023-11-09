<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200930_153858_create_regulation_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%regulation}}', [
            'id' => $this->primaryKey(),
            'related_entity' => $this->string()->null(),
            'confirm_required' => $this->boolean()->null(),
            'before_link_text' => $this->string(1000)->null(),
            'name' => $this->string()->null(),
            'content_type' => $this->integer()->null(),
            'content_link' => $this->string()->null(),
            'content_html' => $this->string(10000)->null(),
            'content_file' => $this->string()->null(),
            'content_file_extension' => $this->string()->null(),
            'attachment_type' => $this->integer()->null(),
        ]);

        
        $this->createIndex(
            '{{%idx-regulation-attachment_type}}',
            '{{%regulation}}',
            'attachment_type'
        );

        
        $this->addForeignKey(
            '{{%fk-regulation-attachment_type}}',
            '{{%regulation}}',
            'attachment_type',
            '{{%attachment_type}}',
            'id',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-regulation-attachment_type}}',
            '{{%regulation}}'
        );

        
        $this->dropIndex(
            '{{%idx-regulation-attachment_type}}',
            '{{%regulation}}'
        );

        $this->dropTable('{{%regulation}}');
    }
}
