<?php

use yii\db\Migration;




class m211004_134911_add_column_to_application_type extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'allow_pick_dates_for_exam', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'allow_pick_dates_for_exam');
    }
}
