<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200610_123716_create_dictionary_budget_level_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->createTable('{{%dictionary_budget_level}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(),
            'ref_key' => $this->string(),
            'parent_key' => $this->string(),
            'description' => $this->string(),
            'data_version' => $this->string(),
            'deletion_mark' => $this->boolean(),
            'predefined' => $this->boolean(),
            'predefined_data_name' => $this->string(),
            'archive' => $this->boolean(),
        ]);
    }

    


    public function down()
    {
        $this->dropTable('{{%dictionary_budget_level}}');
    }
}
