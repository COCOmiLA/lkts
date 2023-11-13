<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221207_083116_remove_archive_column_from_kladr extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->dropIndex('idx_fias_archive', '{{%dictionary_fias}}');
        
        $this->dropColumn('{{%dictionary_fias}}', 'archive');
    }

    


    public function safeDown()
    {
        $this->addColumn('{{%dictionary_fias}}', 'archive', $this->boolean()->defaultValue(false));
        $this->createIndex('idx_fias_archive', '{{%dictionary_fias}}', 'archive');
    }
}
