<?php

use common\components\Migration\MigrationWithDefaultOptions;








class m201014_111256_create_preference_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%preference_attachment}}', [
            'id' => $this->primaryKey(),
            'preference_id' => $this->integer()->null(),
            'attachment_id' => $this->integer()->null(),
        ],$tableOptions);

        
        $this->createIndex(
            '{{%idx-preference_attachment-preference_id}}',
            '{{%preference_attachment}}',
            'preference_id'
        );

        
        $this->addForeignKey(
            '{{%fk-preference_attachment-preference_id}}',
            '{{%preference_attachment}}',
            'preference_id',
            '{{%bachelor_preferences}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-preference_attachment-attachment_id}}',
            '{{%preference_attachment}}',
            'attachment_id'
        );

        
        $this->addForeignKey(
            '{{%fk-preference_attachment-attachment_id}}',
            '{{%preference_attachment}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-preference_attachment-preference_id}}',
            '{{%preference_attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-preference_attachment-preference_id}}',
            '{{%preference_attachment}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-preference_attachment-attachment_id}}',
            '{{%preference_attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-preference_attachment-attachment_id}}',
            '{{%preference_attachment}}'
        );

        $this->dropTable('{{%preference_attachment}}');
    }
}
