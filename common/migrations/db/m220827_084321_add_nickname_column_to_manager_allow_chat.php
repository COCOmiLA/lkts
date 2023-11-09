<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220827_084321_add_nickname_column_to_manager_allow_chat extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%manager_allow_chat}}', 'nickname', $this->string()->defaultValue(null));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%manager_allow_chat}}', 'nickname');
    }
}
