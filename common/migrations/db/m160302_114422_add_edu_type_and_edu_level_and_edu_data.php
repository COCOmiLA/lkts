<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160302_114422_add_edu_type_and_edu_level_and_edu_data extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
          $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_education_type}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'description' => $this->string(1000)->notNull(),
            'parent_key' => $this->string(255),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%dictionary_education_level}}', [
            'id' => $this->primaryKey(),
            'ref_key' => $this->string(255)->notNull(),
            'data_version' => $this->string(100)->notNull(),
            'code' => $this->string(100)->notNull(),
            'type_key' => $this->string(255),
            'description' => $this->string(1000)->notNull(),
            'user_category' => $this->string(1000)->notNull(),
            'short_name' => $this->string(100),
            'level_code' => $this->string(100),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->createTable('{{%education_data}}', [
            'id' => $this->primaryKey(),
            'questionary_id' => $this->integer()->notNull(),
            'education_type_id' => $this->integer()->notNull(),
            'education_level_id' => $this->integer()->notNull(),
            'document_type_id' => $this->integer()->notNull(),
            'series' => $this->string("100"),
            'number' => $this->string("100")->notNull(),
            'date_given' => $this->string("100")->notNull(),
            'edu_end_year' => $this->string("100")->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        
        $this->addForeignKey('fk_education_data_questionary', '{{%education_data}}', 'questionary_id', '{{%abiturient_questionary}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_education_data_education_level', '{{%education_data}}', 'education_level_id', '{{%dictionary_education_level}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_education_data_education_type', '{{%education_data}}', 'education_type_id', '{{%dictionary_education_type}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_education_data_document_type', '{{%education_data}}', 'document_type_id', '{{%dictionary_document_type}}', 'id', 'cascade', 'cascade');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {   
        $this->dropForeignKey('fk_education_data_questionary', '{{%education_data}}');
        $this->dropForeignKey('fk_education_data_education_level', '{{%education_data}}');
        $this->dropForeignKey('fk_education_data_education_type', '{{%education_data}}');
        $this->dropForeignKey('fk_education_data_document_type', '{{%education_data}}');
        
        $this->dropTable('{{%dictionary_education_type}}');
        $this->dropTable('{{%dictionary_education_level}}');
        $this->dropTable('{{%education_data}}');
        
        Yii::$app->db->schema->refresh();
    }
}
