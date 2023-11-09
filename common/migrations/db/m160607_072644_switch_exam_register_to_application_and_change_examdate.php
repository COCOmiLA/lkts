<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160607_072644_switch_exam_register_to_application_and_change_examdate extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        
    $tableOptions = null;
    if ($this->db->driverName === 'mysql') {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
    }
        
       $this->dropColumn('{{%exam_register}}', 'examresult_id');
       $this->addColumn('{{%exam_register}}', 'application_id', $this->integer()->notNull());
       $this->addColumn('{{%exam_register}}', 'entrance_test_discipline_id', $this->integer()->notNull());
       
       $this->dropTable('{{%consult_dates}}');
       $this->dropTable('{{%exam_dates}}');
       
       $this->createTable('{{%exam_dates}}', [
            'id' => $this->primaryKey(),
            'discipline_id' => $this->integer()->notNull(),
            'exam_date_begin' => $this->string(100)->notNull(),
            'exam_date_end' => $this->string(100)->notNull(),
            'consult_date_begin' => $this->string(100)->notNull(),
            'consult_date_end' => $this->string(100)->notNull(),
            'exam_guid' => $this->string(100)->notNull(),
            'consult_guid' => $this->string(100)->notNull(),
            'exam_place' => $this->string(1000),
        ], $tableOptions);
        

       Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
       $this->dropTable('{{%exam_dates}}');
       
        $this->createTable('{{%exam_dates}}', [
            'id' => $this->primaryKey(),
            'discipline_id' => $this->integer()->notNull(),
            'exam_date' => $this->string(100)->notNull(),
            'exam_place' => $this->string(1000),
        ], $tableOptions);
        
        $this->createTable('{{%consult_dates}}', [
            'id' => $this->primaryKey(),
            'discipline_id' => $this->integer()->notNull(),
            'consult_date' => $this->string(100)->notNull(),
            'consult_place' => $this->string(1000),
        ], $tableOptions);
        
       $this->dropColumn('{{%exam_register}}', 'application_id');
       $this->addColumn('{{%exam_register}}', 'examresult_id', $this->integer()->notNull());
       $this->dropColumn('{{%exam_register}}', 'entrance_test_discipline_id');
       
       Yii::$app->db->schema->refresh();
    }
}
