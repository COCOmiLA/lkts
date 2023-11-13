<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210424_101821_add_entrant_manager_id_column_to_change_history_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%change_history}}', 'entrant_manager_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-change_history-entrant_manager_id}}',
            '{{%change_history}}',
            'entrant_manager_id'
        );

        
        $this->addForeignKey(
            '{{%fk-change_history-entrant_manager_id}}',
            '{{%change_history}}',
            'entrant_manager_id',
            '{{%entrant_manager}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-change_history-entrant_manager_id}}',
            '{{%change_history}}'
        );

        
        $this->dropIndex(
            '{{%idx-change_history-entrant_manager_id}}',
            '{{%change_history}}'
        );

        $this->dropColumn('{{%change_history}}', 'entrant_manager_id');
    }
}
