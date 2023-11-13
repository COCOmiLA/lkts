<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\migrations\traits\TableOptionsTrait;




class m210412_134307_create_master_system_manager_interface_setting_table extends MigrationWithDefaultOptions
{
    use TableOptionsTrait;
    


    public function safeUp()
    {
        $this->createTable('{{%master_system_manager_interface_setting}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(500)->null(),
            'value' => $this->string(6000)->null(),
            'type' => $this->string(250)->null(),
        ], self::GetTableOptions());
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%master_system_manager_interface_setting}}');
    }
}
