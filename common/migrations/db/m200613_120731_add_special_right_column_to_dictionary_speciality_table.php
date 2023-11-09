<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200613_120731_add_special_right_column_to_dictionary_speciality_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_speciality}}', 'special_right', $this->boolean());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_speciality}}', 'special_right');
    }
}
