<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210310_065000_add_system_uuid_column_to_user_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'system_uuid', $this->string(36)->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'system_uuid');
    }
}
