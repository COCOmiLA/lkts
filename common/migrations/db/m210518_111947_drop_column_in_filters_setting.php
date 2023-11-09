<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210518_111947_drop_column_in_filters_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->dropColumn('filters_setting', 'description');
    }

    


    public function safeDown()
    {
        $this->addColumn('filters_setting', 'description',  $this->string(500)->defaultValue(''));
    }
}
