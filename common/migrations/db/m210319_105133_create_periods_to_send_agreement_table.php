<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210319_105133_create_periods_to_send_agreement_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%periods_to_send_agreement}}', [
            'id' => $this->primaryKey(),
            'start' => $this->integer()->notNull(),
            'end' => $this->integer()->notNull(),
            'campaign_info_id' => $this->integer()->notNull(),
            'in_day_of_sending_app_only' => $this->boolean()->defaultValue(false),
            'in_day_of_sending_speciality_only' => $this->boolean()->defaultValue(false),
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-periods_to_send_agreement-campaign_info_id}}',
            '{{%periods_to_send_agreement}}',
            'campaign_info_id'
        );
        $this->addForeignKey(
            '{{%fk-periods_to_send_agreement-campaign_info_id}}',
            '{{%periods_to_send_agreement}}',
            'campaign_info_id',
            '{{%campaign_info}}',
            'id',
            'CASCADE'
        );

        $this->addColumn('{{%bachelor_speciality}}', 'sent_to_one_s_at', $this->integer());

    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'sent_to_one_s_at');

        $this->dropForeignKey(
            '{{%fk-periods_to_send_agreement-campaign_info_id}}',
            '{{%periods_to_send_agreement}}'
        );

        $this->dropIndex(
            '{{%idx-periods_to_send_agreement-campaign_info_id}}',
            '{{%periods_to_send_agreement}}'
        );
        $this->dropTable('{{%periods_to_send_agreement}}');
    }
}
