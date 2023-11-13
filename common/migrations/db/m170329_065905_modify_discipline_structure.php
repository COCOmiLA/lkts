<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m170329_065905_modify_discipline_structure extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'parent_id');
        $this->addColumn('{{%dictionary_ege_discipline}}', 'parent_code', $this->string(100));         
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_allowed_forms}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_dicipline_allowed_forms}}', [
            'id' => $this->primaryKey(),
            'discipline_id' => $this->integer()->notNull(),
            'allowed_form_id' => $this->integer()->notNull(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_dicipline_allowed_forms}}');
        $this->dropTable('{{%dictionary_allowed_forms}}');
        
        $this->dropColumn('{{%dictionary_ege_discipline}}', 'parent_code');
        $this->addColumn('{{%dictionary_ege_discipline}}', 'parent_id', $this->integer());
        
        Yii::$app->db->schema->refresh();
    }
}
