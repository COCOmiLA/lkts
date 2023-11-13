<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m230111_091930_add_column_city_code_to_contragent extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_contractor}}', 'location_code', $this->string(100));
        $this->addColumn('{{%dictionary_contractor}}', 'location_name', $this->string(255));
        $this->addColumn('{{%dictionary_contractor}}', 'location_not_found', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_contractor}}', 'location_name');
        $this->dropColumn('{{%dictionary_contractor}}', 'location_code');
        $this->dropColumn('{{%dictionary_contractor}}', 'location_not_found');
    }
}
