<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220111_143844_add_column_table_education_data_filters_filters extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn(
            '{{%education_data_filters}}',
            'allow_profile_input',
            $this->boolean()->defaultValue(false)
        );
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%education_data_filters}}', 'allow_profile_input');
    }
}
