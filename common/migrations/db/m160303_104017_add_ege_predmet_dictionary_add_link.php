<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160303_104017_add_ege_predmet_dictionary_add_link extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_ege_discipline}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'short_name' => $this->string(1000),
            'full_name' => $this->string(1000),
            'parent_key' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->dropColumn('{{%bachelor_egeresult}}', 'discipline_name');
        $this->addColumn('{{%bachelor_egeresult}}', 'discipline_id', $this->integer()->notNull());
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_ege_discipline}}');
        $this->dropColumn('{{%bachelor_egeresult}}', 'discipline_id');
        $this->addColumn('{{%bachelor_egeresult}}', 'discipline_name', $this->string(500)->notNull());
    }
}
