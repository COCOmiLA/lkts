<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200807_110653_add_is_archive_to_user_table extends MigrationWithDefaultOptions
{
    



    public function safeUp()
    {
        $this->addColumn('user', 'is_archive', $this->boolean()->defaultValue(false));
    }

    



    public function safeDown()
    {
        $this->dropColumn('user', 'is_archive');
    }
}
