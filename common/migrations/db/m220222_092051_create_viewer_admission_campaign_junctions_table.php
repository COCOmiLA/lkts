<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220222_092051_create_viewer_admission_campaign_junctions_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable(
            '{{%viewer_admission_campaign_junctions}}',
            [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer(11)->notNull(),
                'application_type_id' => $this->integer(11)->notNull(),
            ]
        );

        $this->createIndex(
            'IDX_vac_application_type_id',
            '{{%viewer_admission_campaign_junctions}}',
            'application_type_id'
        );

        $this->addForeignKey(
            'FK_vac_application_type_id',
            '{{%viewer_admission_campaign_junctions}}',
            'application_type_id',
            'application_type',
            'id'
        );

        $this->createIndex(
            'IDX_vac_user_id',
            '{{%viewer_admission_campaign_junctions}}',
            'user_id'
        );

        $this->addForeignKey(
            'FK_vac_user_id',
            '{{%viewer_admission_campaign_junctions}}',
            'user_id',
            'user',
            'id'
        );
    }

    


    public function safeDown()
    {
        $this->dropIndex('IDX_vac_user_id',             '{{%viewer_admission_campaign_junctions}}');
        $this->dropIndex('IDX_vac_application_type_id', '{{%viewer_admission_campaign_junctions}}');

        $this->dropForeignKey('FK_vac_user_id',             '{{%viewer_admission_campaign_junctions}}');
        $this->dropForeignKey('FK_vac_application_type_id', '{{%viewer_admission_campaign_junctions}}');

        $this->dropTable('{{%viewer_admission_campaign_junctions}}');
    }
}
