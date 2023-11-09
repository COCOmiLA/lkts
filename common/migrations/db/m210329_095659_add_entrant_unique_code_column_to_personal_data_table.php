<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210329_095659_add_entrant_unique_code_column_to_personal_data_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%personal_data}}', 'entrant_unique_code', $this->string(255));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%personal_data}}', 'entrant_unique_code');
    }
}
