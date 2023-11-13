<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210726_094709_add_index_to_address_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->createIndex(
            '{{%idx-address_data-questionary_id}}',
            '{{%address_data}}',
            'questionary_id'
        );
    }

    


    public function safeDown()
    {
        $this->dropIndex('idx-address_data-questionary_id', '{{%address_data}}');
    }
}
