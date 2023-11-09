<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210121_015754_add_ref_id_column_to_admission_campaign_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%admission_campaign}}', 'ref_id', $this->integer()->null());

        
        $this->createIndex(
            '{{%idx-admission_campaign-ref_id}}',
            '{{%admission_campaign}}',
            'ref_id'
        );

        
        $this->addForeignKey(
            '{{%fk-admission_campaign-ref_id}}',
            '{{%admission_campaign}}',
            'ref_id',
            '{{%admission_campaign_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-admission_campaign-ref_id}}',
            '{{%admission_campaign}}'
        );

        
        $this->dropIndex(
            '{{%idx-admission_campaign-ref_id}}',
            '{{%admission_campaign}}'
        );

        $this->dropColumn('{{%admission_campaign}}', 'ref_id');
    }
}
