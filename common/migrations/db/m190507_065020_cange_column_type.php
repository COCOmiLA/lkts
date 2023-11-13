<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m190507_065020_cange_column_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

    }

    


    public function safeDown()
    {
        echo "m190507_065020_cange_column_type cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->alterColumn('address_data', 'area_id',    'string');
        $this->alterColumn('address_data', 'city_id',    'string');
        $this->alterColumn('address_data', 'region_id',  'string');
        $this->alterColumn('address_data', 'street_id',  'string');
        $this->alterColumn('address_data', 'village_id', 'string');
    }
        
    public function down()
    {
        $this->alterColumn('address_data', 'area_id',    'integer');
        $this->alterColumn('address_data', 'city_id',    'integer');
        $this->alterColumn('address_data', 'region_id',  'integer');
        $this->alterColumn('address_data', 'street_id',  'integer');
        $this->alterColumn('address_data', 'village_id', 'integer');
    }
}
