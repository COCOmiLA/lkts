<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221229_090209_alter_order_dates_columns extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->renameColumn('{{%campaign_info}}', 'date_order', 'date_order_end');
        $this->addColumn('{{%campaign_info}}', 'date_order_start', $this->string(100));
    }

    


    public function safeDown()
    {
        
        $this->dropColumn('{{%campaign_info}}', 'date_order_start');
        
        $this->renameColumn('{{%campaign_info}}', 'date_order_end', 'date_order');
    }
}
