<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160506_142805_add_exam_register_model extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
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
        
        $this->createTable('{{%exam_register}}', [
            'id' => $this->primaryKey(),
            'egeresult_id' => $this->integer()->notNull(),
            'exam_date_id' => $this->integer()->notNull(),
            'consult_date_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'decline_comment' => $this->string(1000),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%consult_dates}}');
        $this->dropTable('{{%exam_dates}}');
        $this->dropTable('{{%exam_register}}');
        Yii::$app->db->schema->refresh();
    }
}
