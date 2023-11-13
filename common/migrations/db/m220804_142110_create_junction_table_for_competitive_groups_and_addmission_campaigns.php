<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220804_142110_create_junction_table_for_competitive_groups_and_addmission_campaigns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createTable('{{%admission_campaign_competitive_group_connections}}', [
            'id' => $this->primaryKey(),
            'competitive_group_uid' => $this->string()->notNull(),
            'admission_campaign_uid' => $this->string()->notNull(),
        ]);
    }

    


    public function safeDown()
    {
        
        $this->dropTable('{{%admission_campaign_competitive_group_connections}}');
    }
}
