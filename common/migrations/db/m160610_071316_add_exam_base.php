<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160610_071316_add_exam_base extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
         $this->createTable('{{%dictionary_exam_base}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1000)->notNull(),
            'code' => $this->string(100)->notNull(),
        ], $tableOptions);
         
        $this->addColumn('{{%exam_register}}', 'exam_base_id', $this->integer()->notNull()); 
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%exam_register}}', 'exam_base_id');
        $this->dropTable('{{%dictionary_exam_base}}');
        
        Yii::$app->db->schema->refresh();
    }
}
