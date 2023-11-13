<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220812_095425_create_protal_manager_interface_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%portal_manager_interface_setting}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string(1000)->notNull(),
        ]);
        
        $this->insert('{{%portal_manager_interface_setting}}', [
            'name' => 'need_approvement_and_declination_confirm',
            'description' => 'Показывать модальное окно подтверждения при одобрении и отклонении заявления',
            'value' => '1'
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%portal_manager_interface_setting}}');
    }
}
