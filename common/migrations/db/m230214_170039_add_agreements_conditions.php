<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230214_170039_add_agreements_conditions extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%admission_campaign}}', 'use_common_agreements', $this->boolean()->defaultValue(false));

        $this->createTable('{{%agreement_condition}}', [
            'id' => $this->primaryKey(),
            'campaign_id' => $this->integer(),
            'education_source_ref_id' => $this->integer(),
            'archive' => $this->boolean()->defaultValue(false)
        ]);
        
        $this->addForeignKey(
            '{{%fk_agreement_condition_campaign}}', 
            '{{%agreement_condition}}', 
            'campaign_id', 
            '{{%admission_campaign}}', 
            'id'
        );
        $this->createIndex('{{%idx_campaign_id}}', '{{%agreement_condition}}', 'campaign_id');
        
        $this->addForeignKey(
            '{{%fk_agreement_condition_edu_source}}', 
            '{{%agreement_condition}}', 
            'education_source_ref_id', 
            '{{%education_source_reference_type}}', 
            'id'
        );
        $this->createIndex('{{%idx_education_source_ref_id}}', '{{%agreement_condition}}', 'education_source_ref_id');

        $this->createIndex('{{%idx_campaign_edu_source}}', '{{%agreement_condition}}', ['campaign_id', 'education_source_ref_id'], true);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%admission_campaign}}', 'use_common_agreements');
        $this->dropForeignKey('{{%fk_agreement_condition_campaign}}', '{{%agreement_condition}}');
        $this->dropForeignKey('{{%fk_agreement_condition_edu_source}}', '{{%agreement_condition}}');
        $this->dropTable('{{%agreement_condition}}');
    }
}
