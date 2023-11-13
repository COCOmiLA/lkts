<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230322_133208_periods_of_original_edu_doc_apply extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%periods_of_original_edu_doc_apply}}', [
            'id' => $this->primaryKey(),
            'start' => $this->string(100),
            'end' => $this->string(100),
            'campaign_info_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            '{{%idx-periods_of_original_edu_doc_apply-campaign_info_id}}',
            '{{%periods_of_original_edu_doc_apply}}',
            'campaign_info_id'
        );
        $this->addForeignKey(
            '{{%fk-periods_of_original_edu-campaign_info_id}}',
            '{{%periods_of_original_edu_doc_apply}}',
            'campaign_info_id',
            '{{%campaign_info}}',
            'id',
            'CASCADE'
        );
    }

    


    public function safeDown()
    {
        $this->dropForeignKey(
            '{{%fk-periods_of_original_edu-campaign_info_id}}',
            '{{%periods_of_original_edu_doc_apply}}'
        );

        $this->dropIndex(
            '{{%idx-periods_of_original_edu_doc_apply-campaign_info_id}}',
            '{{%periods_of_original_edu_doc_apply}}'
        );
        $this->dropTable('{{%periods_of_original_edu_doc_apply}}');
    }
}
