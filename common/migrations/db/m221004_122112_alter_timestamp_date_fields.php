<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\bachelor\CampaignInfo;




class m221004_122112_alter_timestamp_date_fields extends MigrationWithDefaultOptions
{

    


    public function safeUp()
    {
        
        $this->renameColumn('{{%campaign_info}}', 'date_start', 'old_date_start');
        $this->renameColumn('{{%campaign_info}}', 'date_final', 'old_date_final');
        $this->renameColumn('{{%campaign_info}}', 'date_order', 'old_date_order');
        
        $this->addColumn('{{%campaign_info}}', 'date_start', $this->string(100));
        $this->addColumn('{{%campaign_info}}', 'date_final', $this->string(100));
        $this->addColumn('{{%campaign_info}}', 'date_order', $this->string(100));
        $this->db->schema->refresh();
        $infos = CampaignInfo::find()->each();
        foreach ($infos as $info) {
            $info->date_start = date('Y-m-d H:i:s', $info->old_date_start);
            $info->date_final = date('Y-m-d H:i:s', $info->old_date_final);
            $info->date_order = date('Y-m-d H:i:s', $info->old_date_order);
            $info->save(false);
        }
        
        $this->dropColumn('{{%campaign_info}}', 'old_date_start');
        $this->dropColumn('{{%campaign_info}}', 'old_date_final');
        $this->dropColumn('{{%campaign_info}}', 'old_date_order');
    }

    


    public function safeDown()
    {
        
        $this->dropColumn('{{%campaign_info}}', 'date_start');
        $this->dropColumn('{{%campaign_info}}', 'date_final');
        $this->dropColumn('{{%campaign_info}}', 'date_order');
        $this->addColumn('{{%campaign_info}}', 'date_start', $this->integer());
        $this->addColumn('{{%campaign_info}}', 'date_final', $this->integer());
        $this->addColumn('{{%campaign_info}}', 'date_order', $this->integer());
    }
}
