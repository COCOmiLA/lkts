<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160715_070421_chance_list_tables extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%chance_list}}', [
            'id' => $this->primaryKey(),
            'date' => $this->string(1000),
            'campaign_code' => $this->integer(),
            'speciality' => $this->string(1000),
            'speciality_code' => $this->string(1000),
            'learnFrom' => $this->string(1000),
            'learnform_code' => $this->integer(),
            'admission_phase' => $this->string(1000),
            'taken_percent' => $this->string(1000),
            'crimea_count' => $this->string(1000),
            'special_count' => $this->string(1000),
            'target_count' => $this->string(1000),
            'competition_count' => $this->string(1000),
            'total_count' => $this->string(1000),
            'filename' => $this->string(1000),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
            
        ], $tableOptions);
        
        $this->createTable('{{%chance_list_rows}}', [
            'id' => $this->primaryKey(),
            'chance_list_id' => $this->integer()->notNull(),
            'user_guid' => $this->string(255),
            'group_code' => $this->integer(),
            'row_number' => $this->integer(),
            'abit_regnumber' => $this->string(1000),
            'fio' => $this->string(1000),
            'speciality_priority' => $this->string(1000),
            'exam_points' => $this->string(1000),
            'id_points' => $this->string(1000),
            'total_points' => $this->string(1000),
            'agreement' => $this->string(1000),
            'special' => $this->string(1000),
            'abiturient_state' => $this->string(1000),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%chance_list_rows}}');
        $this->dropTable('{{%chance_list}}');
        
        Yii::$app->db->schema->refresh();
    }
}
