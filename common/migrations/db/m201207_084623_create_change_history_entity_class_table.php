<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m201207_084623_create_change_history_entity_class_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%change_history_entity_class}}', [
            'id' => $this->primaryKey(),
            'change_id' => $this->integer()->null(),
            'entity_classifier_id' => $this->integer()->null(),
            'change_type' => $this->integer()->null(),
            'entity_identifier' => $this->string(1000)->null(),
            'entity_id' => $this->integer()->null(),
        ],$tableOptions);

        
        $this->createIndex(
            '{{%idx-change_history_entity_class-change_id}}',
            '{{%change_history_entity_class}}',
            'change_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history_entity_class-change_id}}',
            '{{%change_history_entity_class}}',
            'change_id',
            '{{%change_history}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-change_history_entity_class-change_id}}',
            '{{%change_history_entity_class}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history_entity_class-change_id}}',
            '{{%change_history_entity_class}}'
        );

        $this->dropTable('{{%change_history_entity_class}}');
    }
}
