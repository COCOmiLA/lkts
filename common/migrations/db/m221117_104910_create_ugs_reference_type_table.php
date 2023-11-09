<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\components\ReferenceTypeManager\traits\migrations\createDropReferenceTable;




class m221117_104910_create_ugs_reference_type_table extends MigrationWithDefaultOptions
{
    use createDropReferenceTable;

    


    public function safeUp()
    {
        $this->createReferenceTable('ugs_reference_type', self::GetTableOptions());
    }

    


    public function safeDown()
    {
        $this->dropReferenceTable('ugs_reference_type');
    }
}
