<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200612_145302_add_budget_level_column_to_dictionary_speciality_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $this->addColumn('dictionary_speciality', 'budget_level_code', $this->string());
        $this->addColumn('dictionary_speciality', 'budget_level_name', $this->string());
    }

    


    public function down()
    {
        $this->dropColumn('dictionary_speciality', 'budget_level_code');
        $this->dropColumn('dictionary_speciality', 'budget_level_name');
    }
}
