<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\notification\NotificationType;




class m220401_091347_create_notification_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%notification_type}}', [
            'id' => $this->primaryKey(),
            'description' => $this->string(),
            'key' => $this->string()->notNull(),
            'enabled' => $this->boolean()->defaultValue(true),
        ]);
        
        $types = [
            [
                'description' => 'Электронная почта',
                'key' => NotificationType::TYPE_EMAIL,
                'enabled' => true
            ],
            [
                'description' => 'Модуль уведомлений',
                'key' => NotificationType::TYPE_POPUP,
                'enabled' => true
            ],
        ];
        
        foreach ($types as $type) {
            $this->insert('{{%notification_type}}', $type);
        }
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%notification_type}}');
    }
}
