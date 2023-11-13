<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200521_112045_create_bachelor_target_reception_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%bachelor_target_reception}}', [
            'id' => $this->primaryKey(),
            'filename' => $this->string(255),
            'extension' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'name_company' => $this->string(),
            'id_application' => $this->integer(),
            'file' => $this->string(1000)->notNull(),
            'document_series' => $this->string(50)->notNull(),
            'document_number' => $this->string(50)->notNull(),
            'document_organization' => $this->string()->notNull(),
            'document_date' => $this->string()->notNull(),
            'document_type' => $this->string()->notNull(),
            'from1c' => $this->boolean()->defaultValue(false),
            'size' => $this->integer()->defaultValue(0),
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%bachelor_target_reception}}');
    }
}
