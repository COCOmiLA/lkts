<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210910_112233_create_additional_receipt_date_controls_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%additional_receipt_date_controls}}', [
            'id' => $this->primaryKey(),
            'campaign_ref_id' => $this->integer(),
            'competitive_group_ref_id' => $this->integer(),
            'stage' => $this->integer(),
            'date_start' => $this->string(),
            'date_end' => $this->string(),
        ]);

        $this->createIndex(
            '{{%idx-receipt_date-campaign_ref_id}}',
            '{{%additional_receipt_date_controls}}',
            'campaign_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-receipt_date-campaign_ref_id}}',
            '{{%additional_receipt_date_controls}}',
            'campaign_ref_id',
            '{{%admission_campaign_reference_type}}',
            'id',
            'NO ACTION'
        );
        $this->createIndex(
            '{{%idx-receipt_date-competitive_group_ref_id}}',
            '{{%additional_receipt_date_controls}}',
            'competitive_group_ref_id'
        );

        $this->addForeignKey(
            '{{%fk-receipt_date-competitive_group_ref_id}}',
            '{{%additional_receipt_date_controls}}',
            'competitive_group_ref_id',
            '{{%competitive_group_reference_type}}',
            'id',
            'NO ACTION'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-receipt_date-competitive_group_ref_id}}', '{{%additional_receipt_date_controls}}');
        $this->dropForeignKey('{{%fk-receipt_date-campaign_ref_id}}', '{{%additional_receipt_date_controls}}');
        $this->dropIndex('{{%idx-receipt_date-competitive_group_ref_id}}', '{{%additional_receipt_date_controls}}');
        $this->dropIndex('{{%idx-receipt_date-campaign_ref_id}}', '{{%additional_receipt_date_controls}}');

        $this->dropTable('{{%additional_receipt_date_controls}}');
    }
}
