<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200602_061725_create_individual_achievments_document_types_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%individual_achievements_document_types}}', [
            'id' => $this->primaryKey(),
            'document_type' => $this->string(),
            'scan_required' => $this->boolean(),
            'document_description' => $this->string(),
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%individual_achievements_document_types}}');
    }
}
