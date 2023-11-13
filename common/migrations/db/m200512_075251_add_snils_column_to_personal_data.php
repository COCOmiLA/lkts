<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200512_075251_add_snils_column_to_personal_data extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('personal_data', 'snils', $this->string(14)->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('personal_data', 'snils');
    }

    













}
