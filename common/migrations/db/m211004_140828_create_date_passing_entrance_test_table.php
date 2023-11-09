<?php

use common\components\migrations\traits\TableOptionsTrait;
use yii\db\Migration;




class m211004_140828_create_date_passing_entrance_test_table extends Migration
{
    use TableOptionsTrait;

    


    public function safeUp()
    {
        $this->createTable(
            '{{%bachelor_date_passing_entrance_test}}',
            [
                'id' => $this->primaryKey(),

                'bachelor_egeresult_id' => $this->integer()->defaultValue(null),
                'date_time_of_exams_schedule_id' => $this->integer()->defaultValue(null),
                'from_1c' => $this->boolean()->defaultValue(false),

                'created_at' => $this->integer()->defaultValue(null),
                'updated_at' => $this->integer()->defaultValue(null),
            ],
            self::GetTableOptions()
        );

        $this->addForeignKey(
            'FK_to_bachelor_egeresult',
            '{{%bachelor_date_passing_entrance_test}}',
            'bachelor_egeresult_id',
            'bachelor_egeresult',
            'id'
        );
        $this->addForeignKey(
            'FK_to_date_time_of_exams_schedule',
            '{{%bachelor_date_passing_entrance_test}}',
            'date_time_of_exams_schedule_id',
            'dictionary_date_time_of_exams_schedule',
            'id'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('FK_to_bachelor_egeresult', '{{%bachelor_date_passing_entrance_test}}');
        $this->dropForeignKey('FK_to_date_time_of_exams_schedule', '{{%bachelor_date_passing_entrance_test}}');

        $this->dropTable('{{%bachelor_date_passing_entrance_test}}');
    }
}
