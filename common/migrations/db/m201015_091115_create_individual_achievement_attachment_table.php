<?php

use common\components\Migration\MigrationWithDefaultOptions;








class m201015_091115_create_individual_achievement_attachment_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%individual_achievement_attachment}}', [
            'id' => $this->primaryKey(),
            'attachment_id' => $this->integer()->null(),
            'individual_achievement_id' => $this->integer()->null(),
        ], $tableOptions);

        
        $this->createIndex(
            '{{%idx-individual_achievement_attachment-attachment_id}}',
            '{{%individual_achievement_attachment}}',
            'attachment_id'
        );

        
        $this->addForeignKey(
            '{{%fk-individual_achievement_attachment-attachment_id}}',
            '{{%individual_achievement_attachment}}',
            'attachment_id',
            '{{%attachment}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-individual_achievement_attachment-individual_achievement_id}}',
            '{{%individual_achievement_attachment}}',
            'individual_achievement_id'
        );

        
        $this->addForeignKey(
            '{{%fk-individual_achievement_attachment-individual_achievement_id}}',
            '{{%individual_achievement_attachment}}',
            'individual_achievement_id',
            '{{%individual_achievement}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-individual_achievement_attachment-attachment_id}}',
            '{{%individual_achievement_attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-individual_achievement_attachment-attachment_id}}',
            '{{%individual_achievement_attachment}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-individual_achievement_attachment-individual_achievement_id}}',
            '{{%individual_achievement_attachment}}'
        );

        
        $this->dropIndex(
            '{{%idx-individual_achievement_attachment-individual_achievement_id}}',
            '{{%individual_achievement_attachment}}'
        );

        $this->dropTable('{{%individual_achievement_attachment}}');
    }
}
