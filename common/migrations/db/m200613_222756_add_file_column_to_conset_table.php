<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200613_222756_add_file_column_to_conset_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%consent}}', 'file', $this->string(1000));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%consent}}', 'file');
    }
}
