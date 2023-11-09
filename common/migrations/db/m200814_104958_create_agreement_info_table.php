<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200814_104958_create_agreement_info_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%agreement_info}}', [
            'id' => $this->primaryKey(),
            'campaign_id' => $this->integer()->null(),
            'type' => $this->integer()->null(),
            'stage' => $this->integer()->null(),
            'education_form' => $this->string()->null(),
            'date_start' => $this->integer()->null(),
            'date_final' => $this->integer()->null(),
        ]);

        
        $this->createIndex(
            '{{%idx-agreement_info-campaign_id}}',
            '{{%agreement_info}}',
            'campaign_id'
        );

        
        $this->addForeignKey(
            '{{%fk-agreement_info-campaign_id}}',
            '{{%agreement_info}}',
            'campaign_id',
            '{{%admission_campaign}}',
            'id',
            'CASCADE'
        );
        Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        
        $this->dropForeignKey(
            '{{%fk-agreement_info-campaign_id}}',
            '{{%agreement_info}}'
        );

        
        $this->dropIndex(
            '{{%idx-agreement_info-campaign_id}}',
            '{{%agreement_info}}'
        );

        $this->dropTable('{{%agreement_info}}');
    }
}
