<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160609_140832_add_reason_dictionaries extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%user}}', 'username', $this->string(255));
        
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
         $this->createTable('{{%dictionary_dormitory_reason}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1000)->notNull(),
            'code' => $this->string(100)->notNull(),
        ], $tableOptions);
         
        $this->createTable('{{%dictionary_exam_reason}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1000)->notNull(),
            'code' => $this->string(100)->notNull(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%user}}', 'username', $this->string(32));
        $this->dropTable('{{%dictionary_dormitory_reason}}');
        $this->dropTable('{{%dictionary_exam_reason}}');
        
        Yii::$app->db->schema->refresh();
    }
}
