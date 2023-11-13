<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221124_094946_manager_notification_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%manager_notification_settings}}', [
            'id' => $this->primaryKey(),
            'manager_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'value' => $this->string(),
        ]);
        $this->createIndex('idx-manager_notification_settings-manager_id', '{{%manager_notification_settings}}', 'manager_id');
        $this->addForeignKey('fk-manager_notification_settings-manager_id', '{{%manager_notification_settings}}', 'manager_id', '{{%user}}', 'id', 'CASCADE');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk-manager_notification_settings-manager_id', '{{%manager_notification_settings}}');
        $this->dropIndex('idx-manager_notification_settings-manager_id', '{{%manager_notification_settings}}');
        $this->dropTable('{{%manager_notification_settings}}');
    }
}
