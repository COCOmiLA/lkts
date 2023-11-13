<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m170330_071714_add_dictionary_eduowning_form_for_edu extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_ownage_form}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->addColumn('{{%education_data}}', 'ownage_form_id', $this->integer()->notNull());
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_ownage_form}}');
        
        $this->dropColumn('{{%education_data}}', 'ownage_form_id');
    }
}
