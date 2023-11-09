<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221123_133946_add_integration_settings extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%integration_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'value' => $this->string(),
        ]);
        $this->insert(
            '{{%integration_settings}}',
            [
                'name' => 'sms_sender',
                'value' => '',
            ]
        );
        $this->insert(
            '{{%integration_settings}}',
            [
                'name' => 'telegram_bot_sender',
                'value' => '',
            ]
        );
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%integration_settings}}');
    }
}
