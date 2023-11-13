<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220609_113037_create_dictionary_update_history_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%dictionary_update_history}}', [
            'id' => $this->primaryKey(),
            'method_name' => $this->string(),
            'updated_at' => $this->integer(),
        ]);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_update_history}}');
    }
}
