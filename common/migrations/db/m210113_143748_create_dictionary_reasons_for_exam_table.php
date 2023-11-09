<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210113_143748_create_dictionary_reasons_for_exam_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%dictionary_reasons_for_exam}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(),
            'name' => $this->string(),
            'archive' => $this->boolean(),
        ], $tableOptions);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_reasons_for_exam}}');
    }
}
