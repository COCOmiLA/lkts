<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160225_084031_dictionary_init extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dictionary_document_type}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'formula' => $this->string(1000)->notNull(),
            'parent_key' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_citizenship}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_privileges}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'full_name' => $this->string(1000)->notNull(),
            'parent_key' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_special_marks}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'full_name' => $this->string(1000)->notNull(),
            'parent_key' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_admission_base}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'short_name' => $this->string(1000)->notNull(),
            'parent_key' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_admission_categories}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'priority' => $this->string(100)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_admission_features}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
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
        
        $this->createTable('{{%dictionary_foreign_languages}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'parent_key' => $this->string(255),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_document_shipment}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_olympiads}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'full_name' => $this->string(2000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_document_view}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_educational_inst_type}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {    
        $this->dropTable('{{%dictionary_document_type}}');
        $this->dropTable('{{%dictionary_citizenship}}');
        $this->dropTable('{{%dictionary_privileges}}');
        $this->dropTable('{{%dictionary_special_marks}}');
        $this->dropTable('{{%dictionary_admission_base}}');
        $this->dropTable('{{%dictionary_admission_categories}}');
        $this->dropTable('{{%dictionary_admission_features}}');
        $this->dropTable('{{%dictionary_individual_achievement}}');
        $this->dropTable('{{%dictionary_foreign_languages}}');
        $this->dropTable('{{%dictionary_document_shipment}}');
        $this->dropTable('{{%dictionary_olympiads}}');
        $this->dropTable('{{%dictionary_document_view}}');
        $this->dropTable('{{%dictionary_educational_inst_type}}');
        
        Yii::$app->db->schema->refresh();
    }
}
