<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200804_062609_insert_custom_order_column_to_individual_achievements extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('individual_achievements_document_types', 'custom_order', $this->integer());
    }

    



    public function safeDown()
    {
        $this->dropColumn('individual_achievements_document_types', 'custom_order');
    }
}
