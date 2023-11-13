<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160616_115240_add_application_history extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%application_history}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'type' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh(); 
    }

    public function safeDown()
    {
        $this->dropTable('{{%application_history}}');
        Yii::$app->db->schema->refresh();
    }
}
