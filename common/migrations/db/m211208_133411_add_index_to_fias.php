<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211208_133411_add_index_to_fias extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex('idx_fias_archive', '{{%dictionary_fias}}', 'archive');
    }

    


    public function safeDown()
    {
        $this->dropIndex('idx_fias_archive', '{{%dictionary_fias}}');
    }
}
