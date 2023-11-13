<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220222_141740_create_application_type_history_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%application_type_history}}', [
            'id' => $this->primaryKey(),

            'application_type_id' => $this->integer()->notNull(),
            'initiator_id' => $this->integer()->null(),
            'change_type' => $this->string(50)->null(),
            'change_class' => $this->string(500)->null(),

            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'FK_for_application_type_history_ati',
            'application_type_history',
            'application_type_id',
            'application_type',
            'id'
        );

        $this->createIndex(
            'IDX_for_application_type_history_ati',
            'application_type_history',
            'application_type_id'
        );

        $this->addForeignKey(
            'FK_for_application_type_history_ii',
            'application_type_history',
            'initiator_id',
            'user',
            'id'
        );

        $this->createIndex(
            'IDX_for_application_type_history_ii',
            'application_type_history',
            'initiator_id'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_for_application_type_history_ii', 'application_type_history');
        $this->dropIndex('IDX_for_application_type_history_ii', 'application_type_history');
        $this->dropForeignKey('FK_for_application_type_history_ati', 'application_type_history');
        $this->dropIndex('IDX_for_application_type_history_ati', 'application_type_history');

        $this->dropTable('{{%application_type_history}}');
    }
}
