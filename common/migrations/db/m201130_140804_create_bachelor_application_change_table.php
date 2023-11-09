<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m201130_140804_create_bachelor_application_change_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%change_history}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'application_id' => $this->integer()->null(),
            'questionary_id' => $this->integer()->null(),
            'initiator_id' => $this->integer()->null(),
            'change_type' => $this->integer()->null()
        ], $tableOptions);

        
        $this->createIndex(
            '{{%idx-change_history-application_id}}',
            '{{%change_history}}',
            'application_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history-application_id}}',
            '{{%change_history}}',
            'application_id',
            '{{%bachelor_application}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-change_history-questionary_id}}',
            '{{%change_history}}',
            'questionary_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history-questionary_id}}',
            '{{%change_history}}',
            'questionary_id',
            '{{%abiturient_questionary}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-change_history-initiator_id}}',
            '{{%change_history}}',
            'initiator_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history-initiator_id}}',
            '{{%change_history}}',
            'initiator_id',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {

        
        $this->dropForeignKey(
            '{{%fk-change_history-initiator_id}}',
            '{{%change_history}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history-initiator_id}}',
            '{{%change_history}}'
        );
        
        $this->dropForeignKey(
            '{{%fk-change_history-questionary_id}}',
            '{{%change_history}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history-questionary_id}}',
            '{{%change_history}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-change_history-application_id}}',
            '{{%change_history}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history-application_id}}',
            '{{%change_history}}'
        );

        $this->dropTable('{{%change_history}}');
    }
}
