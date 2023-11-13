<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200819_160257_create_document_template_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%document_template}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->null(),
            'file' => $this->string()->null(),
            'filename' => $this->string()->null(),
            'extension' => $this->string()->null(),
            'description' => $this->string(1000)->null(),
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%document_template}}');
    }
}
