<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210517_114950_add_entrant_manager_id_column_to_bachelor_application_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'entrant_manager_blocker_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-bachelor_application-entrant_manager_blocker_id}}',
            '{{%bachelor_application}}',
            'entrant_manager_blocker_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_application-entrant_manager_blocker_id}}',
            '{{%bachelor_application}}',
            'entrant_manager_blocker_id',
            '{{%entrant_manager}}',
            'id',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_application-entrant_manager_blocker_id}}',
            '{{%bachelor_application}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_application-entrant_manager_blocker_id}}',
            '{{%bachelor_application}}'
        );

        $this->dropColumn('{{%bachelor_application}}', 'entrant_manager_blocker_id');
    }
}
