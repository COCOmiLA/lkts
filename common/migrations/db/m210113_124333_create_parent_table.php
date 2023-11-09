<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210113_124333_create_parent_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->dropForeignKey('fk_apersonal_data_questionary', '{{%personal_data}}');
        $this->alterColumn('{{%personal_data}}', 'questionary_id', $this->integer(11)->null());
        $this->addForeignKey('fk_apersonal_data_questionary', '{{%personal_data}}', ['questionary_id'], '{{%abiturient_questionary}}', 'id', 'CASCADE', 'CASCADE');
        
        
        $this->dropForeignKey('fk_passport_data_questionary', '{{%passport_data}}');
        $this->alterColumn('{{%passport_data}}', 'questionary_id', $this->integer(11)->null());
        $this->addForeignKey('fk_passport_data_questionary', '{{%passport_data}}', ['questionary_id'], '{{%abiturient_questionary}}', 'id', 'CASCADE', 'CASCADE');
        
        
        $this->alterColumn('{{%address_data}}', 'questionary_id', $this->integer(11)->null());
        
        
        $this->createTable('{{%parent_data}}', [
            'id' => $this->primaryKey(),
            'questionary_id' => $this->integer()->notNull(),
            'personal_data_id' => $this->integer(),
            'passport_data_id' => $this->integer(),
            'address_data_id' => $this->integer(),
            'type_id' => $this->integer()->notNull(),
            'email' => $this->string(),
            'code' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
        
        $this->addForeignKey('fk_parent_abiturient_questionary', '{{%parent_data}}', ['questionary_id'], '{{%abiturient_questionary}}', ['id']);
        $this->addForeignKey('fk_parent_personal_data', '{{%parent_data}}', ['personal_data_id'], '{{%personal_data}}', ['id']);
        $this->addForeignKey('fk_parent_passport_data', '{{%parent_data}}', ['passport_data_id'], '{{%passport_data}}', ['id']);
        $this->addForeignKey('fk_parent_address_data', '{{%parent_data}}', ['address_data_id'], '{{%address_data}}', ['id']);
        $this->addForeignKey('fk_parent_dictionary_family_type', '{{%parent_data}}', ['type_id'], '{{%dictionary_family_type}}', ['id']);
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('fk_apersonal_data_questionary', '{{%personal_data}}');
        $this->alterColumn('{{%personal_data}}', 'questionary_id', $this->integer(11)->notNull());
        $this->addForeignKey('fk_apersonal_data_questionary', '{{%personal_data}}', ['questionary_id'], '{{%abiturient_questionary}}', 'id', 'CASCADE', 'CASCADE');
        
        $this->dropForeignKey('fk_passport_data_questionary', '{{%passport_data}}');
        $this->alterColumn('{{%passport_data}}', 'questionary_id', $this->integer(11)->notNull());
        $this->addForeignKey('fk_passport_data_questionary', '{{%passport_data}}', ['questionary_id'], '{{%abiturient_questionary}}', 'id', 'CASCADE', 'CASCADE');
        
        $this->alterColumn('{{%address_data}}', 'questionary_id', $this->integer(11)->notNull());
        
        $this->dropForeignKey('fk_parent_abiturient_questionary', '{{%parent_data}}');
        $this->dropForeignKey('fk_parent_personal_data', '{{%parent_data}}');
        $this->dropForeignKey('fk_parent_passport_data', '{{%parent_data}}');
        $this->dropForeignKey('fk_parent_address_data', '{{%parent_data}}');
        $this->dropForeignKey('fk_parent_dictionary_family_type', '{{%parent_data}}');
        
        $this->dropTable('{{%parent_data}}');
    }
}
