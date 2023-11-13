<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210723_090918_add_document_guid_column_to_attachment_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%attachment_type}}', 'document_type_guid', $this->string(255)->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'document_type_guid');
    }
}
