<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160209_090939_settingup_abit_anketa extends MigrationWithDefaultOptions
{
    public function safeUp()
    {   
        $this->addColumn('{{%user_profile}}', 'passport_series', $this->string(50));
        $this->addColumn('{{%user_profile}}', 'passport_number', $this->string(50));
        
         
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%abiturient_questionary}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(100)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'approved_at' => $this->integer(),
            'approver_id' => $this->integer(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_abiturient_questionary_user', '{{%abiturient_questionary}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_abiturient_questionary_approver', '{{%abiturient_questionary}}', 'approver_id', '{{%user}}', 'id', 'set null', 'cascade');
        
        $this->createTable('{{%personal_data}}', [
            'id' => $this->primaryKey(),
            'questionary_id' => $this->integer()->notNull(),
            'firstname' => $this->string()->notNull(),
            'middlename' => $this->string(),
            'lastname' => $this->string()->notNull(),
            'gender' => $this->smallInteger(1),
            'passport_series' => $this->string(50)->notNull(),
            'passport_number' => $this->string(50)->notNull(),
            'birthdate' => $this->string(255)->notNull(),
            'main_phone' => $this->string(50),
            'secondary_phone' => $this->string(50),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
        
        $this->addForeignKey('fk_apersonal_data_questionary', '{{%personal_data}}', 'questionary_id', '{{%abiturient_questionary}}', 'id', 'cascade', 'cascade');
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user_profile}}', 'birthday');
        $this->dropColumn('{{%user_profile}}', 'passport_series');
        $this->dropColumn('{{%user_profile}}', 'passport_number');
        
        $this->dropForeignKey('fk_abiturient_questionary_user', '{{%abiturient_questionary}}');
        $this->dropForeignKey('fk_abiturient_questionary_approver', '{{%abiturient_questionary}}');
        $this->dropForeignKey('fk_personal_data_questionary', '{{%personal_data}}');


        $this->dropTable('{{%personal_data}}');
        $this->dropTable('{{%abiturient_questionary}}'); 

        Yii::$app->db->schema->refresh();
    }
}
