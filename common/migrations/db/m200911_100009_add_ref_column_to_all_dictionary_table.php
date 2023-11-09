<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200911_100009_add_ref_column_to_all_dictionary_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('dictionary_country', 'ref_key', $this->string(255)->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('dictionary_country', 'ref_key');
    }
}
