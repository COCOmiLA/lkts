<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200513_083942_add_document_type_to_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('attachment_type', 'document_type', $this->string());
    }

    


    public function safeDown()
    {
        $this->dropColumn('attachment_type', 'document_type');
    }

    













}
