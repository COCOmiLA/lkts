<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220401_092810_create_notification_setting_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%notification_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'value' => $this->string(1000)->notNull(),
        ]);
        
        $this->insert('{{%notification_settings}}', [
            'name' => 'request_interval',
            'description' => 'Периодичность опроса (сек)',
            'value' => '10'
        ]);
        
        $this->insert('{{%notification_settings}}', [
            'name' => 'enable_widget',
            'description' => 'Виджет уведомлений',
            'value' => '1'
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%notification_settings}}');
    }
}
