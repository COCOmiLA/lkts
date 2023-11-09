<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160404_062135_add_auth_settings extends MigrationWithDefaultOptions
{
   public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%auth_settings}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'value' => $this->string(1000)->notNull(),
        ], $tableOptions);
              
        $this->insert('{{%auth_settings}}', [
            'name' => 'abitcode_enabled',
            'value' => '1',
        ]);
        
        Yii::$app->db->schema->refresh();       
    }

    public function safeDown()
    {
        $this->dropTable('{{%auth_settings}}');
        Yii::$app->db->schema->refresh();
    }
}
