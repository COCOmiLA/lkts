<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\migrations\traits\TableOptionsTrait;








class m210423_140752_create_entrant_manager_table extends MigrationWithDefaultOptions
{
    use TableOptionsTrait;
    


    public function safeUp()
    {
        $this->createTable('{{%entrant_manager}}', [
            'id' => $this->primaryKey(),
            'master_system_manager' => $this->integer()->null(),
            'local_manager' => $this->integer()->null(),
        ], self::GetTableOptions());

        
        $this->createIndex(
            '{{%idx-entrant_manager-master_system_manager}}',
            '{{%entrant_manager}}',
            'master_system_manager'
        );

        
        $this->addForeignKey(
            '{{%fk-entrant_manager-master_system_manager}}',
            '{{%entrant_manager}}',
            'master_system_manager',
            '{{%master_system_manager}}',
            'id',
            'NO ACTION'
        );

        
        $this->createIndex(
            '{{%idx-entrant_manager-local_manager}}',
            '{{%entrant_manager}}',
            'local_manager'
        );

        
        $this->addForeignKey(
            '{{%fk-entrant_manager-local_manager}}',
            '{{%entrant_manager}}',
            'local_manager',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-entrant_manager-master_system_manager}}',
            '{{%entrant_manager}}'
        );

        
        $this->dropIndex(
            '{{%idx-entrant_manager-master_system_manager}}',
            '{{%entrant_manager}}'
        );

        
        $this->dropForeignKey(
            '{{%fk-entrant_manager-local_manager}}',
            '{{%entrant_manager}}'
        );

        
        $this->dropIndex(
            '{{%idx-entrant_manager-local_manager}}',
            '{{%entrant_manager}}'
        );

        $this->dropTable('{{%entrant_manager}}');
    }
}
