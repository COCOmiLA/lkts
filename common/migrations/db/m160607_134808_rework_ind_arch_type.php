<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160607_134808_rework_ind_arch_type extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropTable('{{%dictionary_individual_achievement}}');
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_individual_achievement}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string(1000)->notNull(),
            'campaign_code' => $this->string(100)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_individual_achievement}}');
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_individual_achievement}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'achievement_number' => $this->string(100)->notNull(),
            'short_name' => $this->string(500)->notNull(),
            'countable' => $this->smallInteger()->notNull()->defaultValue(0),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        Yii::$app->db->schema->refresh();
    }
}
