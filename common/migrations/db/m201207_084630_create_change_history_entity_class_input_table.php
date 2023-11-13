<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m201207_084630_create_change_history_entity_class_input_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%change_history_entity_class_input}}', [
            'id' => $this->primaryKey(),
            'value' => $this->string(1000)->null(),
            'old_value' => $this->string(1000)->null(),
            'archived_value' => $this->string(1000)->null(),
            'archived_old_value' => $this->string(1000)->null(),
            'input_name' => $this->string(400)->null(),
            'entity_class_id' => $this->integer()->null(),
        ], $tableOptions);

        
        $this->createIndex(
            '{{%idx-change_history_entity_class_input-entity_class_id}}',
            '{{%change_history_entity_class_input}}',
            'entity_class_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history_entity_class_input-entity_class_id}}',
            '{{%change_history_entity_class_input}}',
            'entity_class_id',
            '{{%change_history_entity_class}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-change_history_entity_class_input-entity_class_id}}',
            '{{%change_history_entity_class_input}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history_entity_class_input-entity_class_id}}',
            '{{%change_history_entity_class_input}}'
        );

        $this->dropTable('{{%change_history_entity_class_input}}');
    }
}
