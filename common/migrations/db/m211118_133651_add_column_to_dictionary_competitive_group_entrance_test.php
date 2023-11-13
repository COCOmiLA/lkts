<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211118_133651_add_column_to_dictionary_competitive_group_entrance_test extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            'dictionary_competitive_group_entrance_test',
            'allow_multiply_alternative_subjects',
            $this->boolean()->defaultValue(false)
        );
    }

    


    public function safeDown()
    {
        $this->dropColumn(
            'dictionary_competitive_group_entrance_test',
            'allow_multiply_alternative_subjects'
        );
    }
}
