<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221205_061644_add_speciality_grouping_modes extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

        
        $this->addColumn('{{%dictionary_speciality}}', 'branch_ref_id', $this->integer());
        $this->createIndex(
            '{{%idx-dictionary_speciality-branch_ref_id}}',
            '{{%dictionary_speciality}}',
            'branch_ref_id'
        );
        $this->addForeignKey(
            '{{%fk-dictionary_speciality-branch_ref_id}}',
            '{{%dictionary_speciality}}',
            'branch_ref_id',
            \common\models\dictionary\StoredReferenceType\StoredSubdivisionReferenceType::tableName(),
            'id',
            'NO ACTION'
        );


        
        $this->createTable('{{%dictionary_speciality_grouping_modes}}', [
            'id' => $this->primaryKey(),
            'description' => $this->string(255)->notNull(),
            'code_name' => $this->string(255)->notNull(),
        ]);
        
        $this->createTable('{{%admission_campaign_grouping_modes_junction}}', [
            'id' => $this->primaryKey(),
            'campaign_id' => $this->integer()->notNull(),
            'grouping_mode_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            '{{%idx-admission_campaign_grouping_modes_junction-campaign_id}}',
            '{{%admission_campaign_grouping_modes_junction}}',
            'campaign_id'
        );
        $this->addForeignKey(
            '{{%fk-campaign_grouping_junction-campaign_id}}',
            '{{%admission_campaign_grouping_modes_junction}}',
            'campaign_id',
            '{{%admission_campaign}}',
            'id',
            'CASCADE'
        );
        $this->createIndex(
            '{{%idx-campaign_grouping_junction-grouping_mode_id}}',
            '{{%admission_campaign_grouping_modes_junction}}',
            'grouping_mode_id'
        );
        $this->addForeignKey(
            '{{%fk-campaign_grouping_junction-grouping_mode_id}}',
            '{{%admission_campaign_grouping_modes_junction}}',
            'grouping_mode_id',
            '{{%dictionary_speciality_grouping_modes}}',
            'id',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-campaign_grouping_junction-grouping_mode_id}}',
            '{{%admission_campaign_grouping_modes_junction}}'
        );
        $this->dropIndex(
            '{{%idx-campaign_grouping_junction-grouping_mode_id}}',
            '{{%admission_campaign_grouping_modes_junction}}'
        );
        $this->dropForeignKey(
            '{{%fk-campaign_grouping_junction-campaign_id}}',
            '{{%admission_campaign_grouping_modes_junction}}'
        );
        $this->dropIndex(
            '{{%idx-admission_campaign_grouping_modes_junction-campaign_id}}',
            '{{%admission_campaign_grouping_modes_junction}}'
        );
        $this->dropTable('{{%admission_campaign_grouping_modes_junction}}');
        
        $this->dropTable('{{%dictionary_speciality_grouping_modes}}');

        $this->dropForeignKey('{{%fk-dictionary_speciality-branch_ref_id}}', '{{%dictionary_speciality}}');
        $this->dropIndex('{{%idx-dictionary_speciality-branch_ref_id}}', '{{%dictionary_speciality}}');
        $this->dropColumn('{{%dictionary_speciality}}', 'branch_ref_id');
    }
}
