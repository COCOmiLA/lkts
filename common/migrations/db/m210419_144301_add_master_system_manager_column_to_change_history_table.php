<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210419_144301_add_master_system_manager_column_to_change_history_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%change_history}}', 'master_system_manager_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-change_history-master_system_manager_id}}',
            '{{%change_history}}',
            'master_system_manager_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history-master_system_manager_id}}',
            '{{%change_history}}',
            'master_system_manager_id',
            '{{%master_system_manager}}',
            'id',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-change_history-master_system_manager_id}}',
            '{{%change_history}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history-master_system_manager_id}}',
            '{{%change_history}}'
        );

        $this->dropColumn('{{%change_history}}', 'master_system_manager_id');
    }
}
