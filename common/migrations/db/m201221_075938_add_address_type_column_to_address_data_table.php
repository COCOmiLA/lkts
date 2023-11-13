<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\abiturient\models\AddressData;




class m201221_075938_add_address_type_column_to_address_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%address_data}}', 'address_type', $this->integer()->null());
        AddressData::updateAll([
            'address_type' => AddressData::ADDRESS_TYPE_REGISTRATION
        ]);
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%address_data}}', 'address_type');
    }
}
