<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160418_083941_add_competition_list extends MigrationWithDefaultOptions
{
    public function safeUp() 
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%competition_list}}', [
            'id' => $this->primaryKey(),
            'date' => $this->string(1000),
            'qualification'=>$this->string(1000),
            'learnForm'=>$this->string(1000),
            'financeForm'=>$this->string(1000),
            'institute'=>$this->string(1000),
            'speciality'=>$this->string(1000),
            'speciality_code'=>$this->string(1000),
            'crimea_count'=>$this->string(1000),
            'special_count'=>$this->string(1000),
            'target_count' => $this->string(1000),
            'competition_count' => $this->string(1000),
            'total_count' => $this->string(1000),
            'exam1' => $this->string(1000),
            'exam1code' => $this->string(1000),
            'exam2' => $this->string(1000),
            'exam2code' => $this->string(1000),
            'exam3' => $this->string(1000),
            'exam3code' => $this->string(1000),
            'filename' => $this->string(1000),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%competition_list_rows}}', [
            'id' => $this->primaryKey(),
            'competition_list_id' => $this->integer()->notNull(),
            'row_number' => $this->string(1000),
            'abit_regnumber'=>$this->string(1000),
            'fio'=>$this->string(1000),
            'total_points'=>$this->string(1000),
            'total_exam_points'=>$this->string(1000),
            'exam1_points'=>$this->string(1000),
            'exam2_points'=>$this->string(1000),
            'exam3_points'=>$this->string(1000),
            'id_points' => $this->string(1000),
            'speciality_priority' => $this->string(1000),
            'have_original' => $this->string(1000),
            'admission_condition' => $this->string(1000),
            'need_dormitory' => $this->string(1000),
            'abit_state' => $this->string(1000),
            'abit_code' => $this->string(100),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%competition_list}}');
        $this->dropTable('{{%competition_list_rows}}');
        Yii::$app->db->schema->refresh();
    }
}
