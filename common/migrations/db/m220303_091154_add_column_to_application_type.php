<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220303_091154_add_column_to_application_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'can_change_date_exam_from_1c', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'can_change_date_exam_from_1c');
    }
}
