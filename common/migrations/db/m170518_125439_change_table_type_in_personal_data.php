<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m170518_125439_change_table_type_in_personal_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->alterColumn('{{%personal_data}}', 'gender', $this->string(9));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%personal_data}}', 'gender', $this->smallInteger(1));
    }
}
