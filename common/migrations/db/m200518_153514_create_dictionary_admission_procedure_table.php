<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200518_153514_create_dictionary_admission_procedure_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%dictionary_admission_procedure}}', true) !== null) {
            $this->dropTable('{{%dictionary_admission_procedure}}');
        }
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dictionary_admission_procedure}}', [
            'id' => $this->primaryKey(),
            'id_pk' => $this->string(),
            'finance_code' => $this->string(),
            'category_code' => $this->string(),
            'special_mark_code' => $this->string(),
            'privilege_code' => $this->string(),
            'individual_value' => $this->boolean(),
            'priority_right' => $this->boolean(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
            'archive' => $this->boolean()->notNull()->defaultValue(false)
        ], $tableOptions);
    }

    


    public function safeDown()
    {
        $this->dropTable('{{%dictionary_admission_procedure}}');
    }
}
