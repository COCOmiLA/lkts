<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m201015_171706_add_birth_place_column_to_personal_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%personal_data}}', 'birth_place', $this->string(255)->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%personal_data}}', 'birth_place');
    }
}
