<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200923_083925_add_category_code_column_to_campaign_info_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%campaign_info}}', 'category_code', $this->string());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%campaign_info}}', 'category_code');
    }
}
