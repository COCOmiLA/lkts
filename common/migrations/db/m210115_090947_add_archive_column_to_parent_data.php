<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210115_090947_add_archive_column_to_parent_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%parent_data}}', 'archive', $this->boolean());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%parent_data}}', 'archive');
    }
}
