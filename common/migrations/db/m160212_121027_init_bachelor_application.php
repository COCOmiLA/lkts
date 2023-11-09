<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160212_121027_init_bachelor_application extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bachelor_application}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(100)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'approved_at' => $this->integer(),
            'approver_id' => $this->integer(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_bachelor_application_user', '{{%bachelor_application}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_bachelor_application_approver', '{{%bachelor_application}}', 'approver_id', '{{%user}}', 'id', 'set null', 'cascade');
        
        $this->createTable('{{%bachelor_egeyear}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'year_number' => $this->string(100)->notNull(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_bachelor_egeyear_application', '{{%bachelor_egeyear}}', 'application_id', '{{%bachelor_application}}', 'id', 'cascade', 'cascade');
        
        $this->createTable('{{%bachelor_egeresult}}', [
            'id' => $this->primaryKey(),
            'egeyear_id' => $this->integer()->notNull(),
            'discipline_name' => $this->string(500)->notNull(),
            'discipline_points' => $this->string(100)->notNull(),
            'status' => $this->string(100)->notNull(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_bachelor_egeresult_egeyear', '{{%bachelor_egeresult}}', 'egeyear_id', '{{%bachelor_egeyear}}', 'id', 'cascade', 'cascade');
        
        $this->createTable('{{%bachelor_speciality}}', [
            'id' => $this->primaryKey(),
            'application_id' => $this->integer()->notNull(),
            'name' => $this->string(1000)->notNull(),
            'speciality_code' => $this->string(200)->notNull(),
            'learn_level' => $this->string(1000)->notNull(),
            'learn_form' => $this->string(1000)->notNull(),
            'funding_source' => $this->string(1000)->notNull(),
            'educational_program' => $this->string(1000)->notNull(),
            'department' => $this->string(1000)->notNull(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_bachelor_speciality_application', '{{%bachelor_speciality}}', 'application_id', '{{%bachelor_application}}', 'id', 'cascade', 'cascade');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_bachelor_application_user', '{{%bachelor_application}}');
        $this->dropForeignKey('fk_bachelor_application_approver', '{{%bachelor_application}}');
        $this->dropForeignKey('fk_bachelor_egeyear_application', '{{%bachelor_egeyear}}');
        $this->dropForeignKey('fk_bachelor_egeresult_egeyear', '{{%bachelor_egeresult}}');
        $this->dropForeignKey('fk_bachelor_speciality_application', '{{%bachelor_speciality}}');
        
        $this->dropTable('{{%bachelor_speciality}}');
        $this->dropTable('{{%bachelor_egeresult}}');
        $this->dropTable('{{%bachelor_egeyear}}');
        $this->dropTable('{{%bachelor_application}}');
        
        Yii::$app->db->schema->refresh();
    }
}
