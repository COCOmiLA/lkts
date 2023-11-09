<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160318_065935_add_settings_table extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%sandbox_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'value' => $this->string(1000)->notNull(),
        ], $tableOptions);
              
        $this->insert('{{%sandbox_settings}}', [
            'name' => 'sandbox_enabled',
            'value' => '1',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->dropTable('{{%sandbox_settings}}');
        Yii::$app->db->schema->refresh();
    }
}
