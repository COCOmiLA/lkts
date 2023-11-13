<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200803_103645_insert_custom_order_column_to_attachment_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('attachment_type', 'custom_order', $this->integer());
    }

    



    public function safeDown()
    {
        $this->dropColumn('attachment_type', 'custom_order');
    }
}
