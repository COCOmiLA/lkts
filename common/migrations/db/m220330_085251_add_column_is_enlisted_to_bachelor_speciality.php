<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220330_085251_add_column_is_enlisted_to_bachelor_speciality extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'is_enlisted', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'is_enlisted');
    }
}
