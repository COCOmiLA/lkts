<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220720_094043_alter_phone_column_size extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->alterColumn('{{%personal_data}}', 'main_phone', $this->string(255));
        $this->alterColumn('{{%personal_data}}', 'secondary_phone', $this->string(255));
    }

    


    public function safeDown()
    {
        $this->alterColumn('{{%personal_data}}', 'main_phone', $this->string(50));
        $this->alterColumn('{{%personal_data}}', 'secondary_phone', $this->string(50));
    }
}
