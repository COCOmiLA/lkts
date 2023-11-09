<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200613_221601_add_filename_column_to_conset_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%consent}}', 'filename', $this->string(1000));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%consent}}', 'filename');
    }
}
