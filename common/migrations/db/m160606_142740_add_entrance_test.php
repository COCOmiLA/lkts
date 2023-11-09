<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160606_142740_add_entrance_test extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_entrance_test_discipline}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(1000)->notNull(),
            'discipline_name' => $this->string(1000)->notNull(),
            'campaign_code' => $this->string(100),
        ], $tableOptions);
        
        $this->createTable('{{%exam_result}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'entrance_test_discipline_id' => $this->integer()->notNull(),
            'discipline_points' => $this->string(100),
            'exam_form' => $this->string(1000),
            'exam_register' => $this->smallInteger()->defaultValue(0),
        ], $tableOptions);
        
        $this->dropColumn('{{%bachelor_egeresult}}', 'exam_register');
        $this->alterColumn('{{%bachelor_egeresult}}', 'discipline_points', $this->string(100)->notNull());
        
        $this->dropColumn('{{%exam_register}}', 'egeresult_id');
        $this->addColumn('{{%exam_register}}', 'examresult_id', $this->integer()->notNull());
        
         Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
       $this->dropTable('{{%dictionary_entrance_test_discipline}}');
       $this->dropTable('{{%exam_result}}');
       
       $this->addColumn('{{%bachelor_egeresult}}', 'exam_register', $this->smallInteger()->defaultValue(0));
       $this->alterColumn('{{%bachelor_egeresult}}', 'discipline_points', $this->string(100));
       
       $this->dropColumn('{{%exam_register}}', 'examresult_id');
       $this->addColumn('{{%exam_register}}', 'egeresult_id', $this->integer()->notNull());
       
       Yii::$app->db->schema->refresh();
    }
}
