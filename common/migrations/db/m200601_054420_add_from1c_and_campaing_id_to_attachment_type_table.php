<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200601_054420_add_from1c_and_campaing_id_to_attachment_type_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('attachment_type', 'from1c', $this->boolean());
        $this->addColumn('attachment_type', 'campaign_code', $this->string());
    }

    


    public function safeDown()
    {
        $this->dropColumn('attachment_type', 'from1c');
        $this->dropColumn('attachment_type', 'campaign_code');
    }
}
