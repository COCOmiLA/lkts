<?php

use common\components\Migration\MigrationWithDefaultOptions;








class m201013_121639_create_target_reception_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%target_reception_attachment}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer()->null(),
            'target_reception_id' => $this->integer()->null(),
        ],$tableOptions);

        
        $this->createIndex(
            '{{%idx-target_reception_attachment-attachment_id}}',
            '{{%target_reception_attachment}}',
            'attachment_id'
        );

        
        $this->addForeignKey(
            '{{%fk-target_reception_attachment-attachment_id}}',
            '{{%target_reception_attachment}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-target_reception_attachment-target_reception_id}}',
            '{{%target_reception_attachment}}',
            'target_reception_id'
        );

        
        $this->addForeignKey(
            '{{%fk-target_reception_attachment-target_reception_id}}',
            '{{%target_reception_attachment}}',
            'target_reception_id',
            '{{%bachelor_target_reception}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-target_reception_attachment-attachment_id}}',
            '{{%target_reception_attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-target_reception_attachment-attachment_id}}',
            '{{%target_reception_attachment}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-target_reception_attachment-target_reception_id}}',
            '{{%target_reception_attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-target_reception_attachment-target_reception_id}}',
            '{{%target_reception_attachment}}'
        );

        $this->dropTable('{{%target_reception_attachment}}');
    }
}
