<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210203_095222_create_entrant_application_master_server_history_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%entrant_application_master_server_history}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->null(),
            'created_at' => $this->integer(),
            'status' => $this->integer(),
        ]);

        
        $this->createIndex(
            '{{%idx-entrant_application_master_server_history-application_id}}',
            '{{%entrant_application_master_server_history}}',
            'application_id'
        );

        
        $this->addForeignKey(
            '{{%fk-entrant_application_master_server_history-application_id}}',
            '{{%entrant_application_master_server_history}}',
            'application_id',
            '{{%bachelor_application}}',
            'id',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-entrant_application_master_server_history-application_id}}',
            '{{%entrant_application_master_server_history}}'
        );

        
        $this->dropIndex(
            '{{%idx-entrant_application_master_server_history-application_id}}',
            '{{%entrant_application_master_server_history}}'
        );

        $this->dropTable('{{%entrant_application_master_server_history}}');
    }
}
