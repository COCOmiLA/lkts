<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200518_202152_create_dictionary_document_privileges_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%dictionary_document_abiturient_type}}', [
            'id' => $this->primaryKey(),
            'id_pk' => $this->string(),
            'document_set_code' => $this->string(),
            'document_type_code' => $this->string(),
            'number_document' => $this->boolean(),
            'scan_required' => $this->boolean(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
            'archive' => $this->boolean()->notNull()->defaultValue(false)

        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_document_abiturient_type}}');
    }
}
