<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221107_123547_add_contractor_links extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%passport_data}}', 'contractor_id', $this->integer());
        $this->createIndex('{{%idx_passport_data_contractor}}', '{{%passport_data}}', 'contractor_id');
        $this->addForeignKey('{{%fk_passport_data_contractor}}', '{{%passport_data}}', 'contractor_id', '{{%dictionary_contractor}}', 'id');
        
        $this->alterColumn('{{%education_data}}', 'school_name', $this->string(1000)->defaultValue(null));
        $this->addColumn('{{%education_data}}','contractor_id', $this->integer());
        $this->createIndex('{{%idx_education_data_contractor}}', '{{%education_data}}', 'contractor_id');
        $this->addForeignKey('{{%fk_education_data_contractor}}', '{{%education_data}}', 'contractor_id', '{{%dictionary_contractor}}', 'id');
        
        $this->addColumn('{{%bachelor_preferences}}','contractor_id', $this->integer());
        $this->createIndex('{{%idx_bachelor_preferences_contractor}}', '{{%bachelor_preferences}}', 'contractor_id');
        $this->addForeignKey('{{%fk_bachelor_preferences_contractor}}', '{{%bachelor_preferences}}', 'contractor_id', '{{%dictionary_contractor}}', 'id');
        
        $this->addColumn('{{%bachelor_target_reception}}','target_contractor_id', $this->integer());
        $this->createIndex('{{%idx_bachelor_target_reception_contractor}}', '{{%bachelor_target_reception}}', 'target_contractor_id');
        $this->addForeignKey('{{%fk_bachelor_target_reception_contractor}}', '{{%bachelor_target_reception}}', 'target_contractor_id', '{{%dictionary_contractor}}', 'id');
        $this->addColumn('{{%bachelor_target_reception}}','document_contractor_id', $this->integer());
        $this->createIndex('{{%idx_bachelor_target_reception_document_contractor}}', '{{%bachelor_target_reception}}', 'document_contractor_id');
        $this->addForeignKey('{{%fk_bachelor_target_reception_document_contractor}}', '{{%bachelor_target_reception}}', 'document_contractor_id', '{{%dictionary_contractor}}', 'id');
        
        $this->addColumn('{{%individual_achievement}}','contractor_id', $this->integer());
        $this->createIndex('{{%idx_individual_achievement_contractor}}', '{{%individual_achievement}}', 'contractor_id');
        $this->addForeignKey('{{%fk_individual_achievement_contractor}}', '{{%individual_achievement}}', 'contractor_id', '{{%dictionary_contractor}}', 'id');
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_passport_data_contractor}}', '{{%passport_data}}');
        $this->dropIndex('{{%idx_passport_data_contractor}}', '{{%passport_data}}');
        $this->dropColumn('{{%passport_data}}', 'contractor_id');

        $this->alterColumn('{{%education_data}}', 'school_name', $this->string(1000));
        $this->dropForeignKey('{{%fk_education_data_contractor}}', '{{%education_data}}');
        $this->dropIndex('{{%idx_education_data_contractor}}', '{{%education_data}}');
        $this->dropColumn('{{%education_data}}', 'contractor_id');
        
        $this->dropForeignKey('{{%fk_bachelor_preferences_contractor}}', '{{%bachelor_preferences}}');
        $this->dropIndex('{{%idx_bachelor_preferences_contractor}}', '{{%bachelor_preferences}}');
        $this->dropColumn('{{%bachelor_preferences}}', 'contractor_id');

        $this->dropForeignKey('{{%fk_bachelor_target_reception_contractor}}', '{{%bachelor_target_reception}}');
        $this->dropIndex('{{%idx_bachelor_target_reception_contractor}}', '{{%bachelor_target_reception}}');
        $this->dropColumn('{{%bachelor_target_reception}}', 'target_contractor_id');
        $this->dropForeignKey('{{%fk_bachelor_target_reception_document_contractor}}', '{{%bachelor_target_reception}}');
        $this->dropIndex('{{%idx_bachelor_target_reception_document_contractor}}', '{{%bachelor_target_reception}}');
        $this->dropColumn('{{%bachelor_target_reception}}', 'document_contractor_id');

        $this->dropForeignKey('{{%fk_individual_achievement_contractor}}', '{{%individual_achievement}}');
        $this->dropIndex('{{%idx_individual_achievement_contractor}}', '{{%individual_achievement}}');
        $this->dropColumn('{{%individual_achievement}}', 'contractor_id');
    }
}
