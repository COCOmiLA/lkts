<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m201103_114848_add_last_manager_id_and_lats_management_at_column_to_bachelor_application_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'last_manager_id', $this->integer()->null());
        $this->addColumn('{{%bachelor_application}}', 'last_management_at', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-bachelor_application-last_manager_id}}',
            '{{%bachelor_application}}',
            'last_manager_id'
        );

        
        $this->addForeignKey(
            '{{%fk-bachelor_application-last_manager_id}}',
            '{{%bachelor_application}}',
            'last_manager_id',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-bachelor_application-last_manager_id}}',
            '{{%bachelor_application}}'
        );

        
        $this->dropIndex(
            '{{%idx-bachelor_application-last_manager_id}}',
            '{{%bachelor_application}}'
        );

        $this->dropColumn('{{%bachelor_application}}', 'last_manager_id');
        $this->dropColumn('{{%bachelor_application}}', 'last_management_at');
    }
}
