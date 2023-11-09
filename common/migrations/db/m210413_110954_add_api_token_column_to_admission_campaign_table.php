<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210413_110954_add_api_token_column_to_admission_campaign_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%admission_campaign}}', 'api_token', $this->string()->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%admission_campaign}}', 'api_token');
    }
}
