<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210120_155312_create_dictinonary_gender_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dictionary_gender}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
            'archive' => $this->boolean()->null()
        ], $tableOptions);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_gender}}');
    }
}
