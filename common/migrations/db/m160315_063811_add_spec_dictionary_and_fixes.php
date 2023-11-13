<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160315_063811_add_spec_dictionary_and_fixes extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dictionary_speciality}}', [
            'id' => $this->primaryKey(),
            'faculty_code' => $this->string(100)->notNull(),
            'faculty_name' => $this->string(1000)->notNull(),
            'speciality_code' => $this->string(100)->notNull(),
            'speciality_name' => $this->string(1000)->notNull(),
            'profil_code' => $this->string(100),
            'profil_name' => $this->string(1000),
            'edulevel_code' => $this->string(100),
            'edulevel_name' => $this->string(1000),
            'eduform_code' => $this->string(100),
            'eduform_name' => $this->string(1000),
            'eduprogram_code' => $this->string(100),
            'eduprogram_name' => $this->string(1000),
            'finance_code' => $this->string(100),
            'finance_name' => $this->string(1000),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ], $tableOptions);
        
        $this->dropColumn('{{%bachelor_speciality}}', 'faculty_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'speciality_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'educationlevel_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'educationform_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'finance_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'profil_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'eduprogram_1c_code');
        
        $this->dropColumn('{{%bachelor_speciality}}', 'name');
        $this->dropColumn('{{%bachelor_speciality}}', 'speciality_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'learn_level');
        $this->dropColumn('{{%bachelor_speciality}}', 'learn_form');
        $this->dropColumn('{{%bachelor_speciality}}', 'funding_source');
        $this->dropColumn('{{%bachelor_speciality}}', 'educational_program');
        $this->dropColumn('{{%bachelor_speciality}}', 'department');
        
        $this->addColumn('{{%bachelor_speciality}}', 'speciality_id', $this->integer()->notNull());
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropTable('{{%dictionary_speciality}}');
        $this->dropColumn('{{%bachelor_speciality}}', 'speciality_id');
        
        $this->addColumn('{{%bachelor_speciality}}', 'faculty_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'speciality_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'educationlevel_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'educationform_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'finance_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'profil_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'eduprogram_1c_code', $this->string("100"));
        
        $this->addColumn('{{%bachelor_speciality}}', 'name', $this->string(1000)->notNull());
        $this->addColumn('{{%bachelor_speciality}}', 'speciality_code', $this->string(200)->notNull());
        $this->addColumn('{{%bachelor_speciality}}', 'learn_level', $this->string(1000)->notNull());
        $this->addColumn('{{%bachelor_speciality}}', 'learn_form', $this->string(1000)->notNull());
        $this->addColumn('{{%bachelor_speciality}}', 'funding_source', $this->string(1000)->notNull());
        $this->addColumn('{{%bachelor_speciality}}', 'educational_program', $this->string(1000)->notNull());
        $this->addColumn('{{%bachelor_speciality}}', 'department', $this->string(1000)->notNull());
        
        Yii::$app->db->schema->refresh();
    }
}
