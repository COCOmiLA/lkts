<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160708_122434_add_application_history extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%application_moderate_history}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'comment' => $this->string(2000),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%application_moderate_history}}');
        
        Yii::$app->db->schema->refresh();
    }
}
