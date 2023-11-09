<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220224_075007_create_application_type_history_entity_class_input_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%application_type_history_entity_class_input}}', [
            'id' => $this->primaryKey(),

            'application_type_history_id' => $this->integer()->notNull(),
            'input_name' => $this->string()->null(),
            'actual_value' => $this->string()->null(),
            'old_value' => $this->string()->null(),

            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'FK_for_application_type_history_entity',
            'application_type_history_entity_class_input',
            'application_type_history_id',
            'application_type_history',
            'id'
        );

        $this->createIndex(
            'IDX_for_application_type_history_entity',
            'application_type_history_entity_class_input',
            'application_type_history_id'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_for_application_type_history_entity', 'application_type_history_entity_class_input');
        $this->dropIndex('IDX_for_application_type_history_entity', 'application_type_history_entity_class_input');

        $this->dropTable('{{%application_type_history_entity_class_input}}');
    }
}
