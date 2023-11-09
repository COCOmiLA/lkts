<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200612_214139_add_child_discipline_code_column_to_bachelor_egeresult_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_egeresult}}', 'child_discipline_code', $this->string());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_egeresult}}', 'child_discipline_code');
    }
}
