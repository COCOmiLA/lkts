<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220422_072641_add_synced_with_1C_at_column extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_application}}', 'synced_with_1C_at', $this->integer(11));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_application}}', 'synced_with_1C_at');
    }
}
