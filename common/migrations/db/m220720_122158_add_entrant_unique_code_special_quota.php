<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220720_122158_add_entrant_unique_code_special_quota extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->addColumn('{{%personal_data}}', 'entrant_unique_code_special_quota', $this->string(255));
    }

    


    public function safeDown()
    {
        
        $this->dropColumn('{{%personal_data}}', 'entrant_unique_code_special_quota');
    }
}
