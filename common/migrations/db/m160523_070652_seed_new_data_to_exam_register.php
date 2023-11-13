<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160523_070652_seed_new_data_to_exam_register extends MigrationWithDefaultOptions
{
    public function safeUp()
    {    
        $this->delete('{{%exam_dates}}');
        $this->delete('{{%consult_dates}}');
        
        $dicipline_ids = [1,2,4,5];
        $i=1;
        $j=1;
        foreach($dicipline_ids as $dicipline){
            
        $this->insert('{{%exam_dates}}', [
            'id' => $i,
            'exam_date' => '11.05.2016 с 11:00 по 13:00',
            'exam_place' => 'аудитория 201',
            'discipline_code' => $dicipline,
        ]); 
        $i++;
        $this->insert('{{%exam_dates}}', [
            'id' => $i,
            'exam_date' => '12.05.2016 с 11:00 по 13:00',
            'exam_place' => 'аудитория 202',
            'discipline_code' => $dicipline,
        ]); 
        $i++;
        $this->insert('{{%exam_dates}}', [
            'id' => $i,
            'exam_date' => '13.05.2016 с 11:00 по 13:00',
            'exam_place' => 'аудитория 203',
            'discipline_code' => $dicipline,
        ]); 
        $i++;
        $this->insert('{{%exam_dates}}', [
            'id' => $i,
            'exam_date' => '14.05.2016 с 11:00 по 13:00',
            'exam_place' => 'аудитория 204',
            'discipline_code' => $dicipline,
        ]); 
        $i++;
            
        $this->insert('{{%consult_dates}}', [
            'id' => $j,
            'consult_date' => '10.05.2016 с 9:00 по 10:00',
            'consult_place' => 'аудитория 104',
            'discipline_code' => $dicipline,
        ]); 
        $j++;
        $this->insert('{{%consult_dates}}', [
            'id' => $j,
            'consult_date' => '11.05.2016 с 9:00 по 10:00',
            'consult_place' => 'аудитория 104',
            'discipline_code' => $dicipline,
        ]); 
        $j++;
        $this->insert('{{%consult_dates}}', [
            'id' => $j,
            'consult_date' => '12.05.2016 с 9:00 по 10:00',
            'consult_place' => 'аудитория 104',
            'discipline_code' => $dicipline,
        ]); 
        $j++;
        $this->insert('{{%consult_dates}}', [
            'id' => $j,
            'consult_date' => '13.05.2016 с 9:00 по 10:00',
            'consult_place' => 'аудитория 104',
            'discipline_code' => $dicipline,
        ]); 
        $j++;
            
        }
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->delete('{{%exam_dates}}');
        $this->delete('{{%consult_dates}}');
        Yii::$app->db->schema->refresh();
    }
}
