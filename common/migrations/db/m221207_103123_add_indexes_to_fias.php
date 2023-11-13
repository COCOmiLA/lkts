<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221207_103123_add_indexes_to_fias extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->createIndex('idx_fias_id_region_code', '{{%dictionary_fias}}', ['fias_id', 'region_code']);
    }

    


    public function safeDown()
    {
        $this->dropIndex('idx_fias_id_region_code', '{{%dictionary_fias}}');
    }
}
