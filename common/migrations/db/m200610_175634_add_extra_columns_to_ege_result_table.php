<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200610_175634_add_extra_columns_to_ege_result_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('dictionary_ege_discipline', 'group_code', $this->string());
    }

    


    public function down()
    {
        $this->dropColumn('dictionary_ege_discipline', 'group_code');
    }
}
