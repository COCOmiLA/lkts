<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\migrations\traits\TableOptionsTrait;







class m210415_131239_create_master_system_manager_table extends MigrationWithDefaultOptions
{
    use TableOptionsTrait;
    


    public function safeUp()
    {
        $this->createTable('{{%master_system_manager}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(1000)->null(),
            'ref_id' => $this->integer()->null(),
        ], self::GetTableOptions());

        
        $this->createIndex(
            '{{%idx-master_system_manager-ref_id}}',
            '{{%master_system_manager}}',
            'ref_id'
        );

        
        $this->addForeignKey(
            '{{%fk-master_system_manager-ref_id}}',
            '{{%master_system_manager}}',
            'ref_id',
            '{{%user_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-master_system_manager-ref_id}}',
            '{{%master_system_manager}}'
        );

        
        $this->dropIndex(
            '{{%idx-master_system_manager-ref_id}}',
            '{{%master_system_manager}}'
        );

        $this->dropTable('{{%master_system_manager}}');
    }
}
