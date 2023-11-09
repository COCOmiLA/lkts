<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211215_131121_remove_code_column extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->dropColumn('{{%bachelor_preferences}}', 'code');
    }

    


    public function safeDown()
    {
        $this->addColumn('{{%bachelor_preferences}}', 'code', $this->string());
    }
}
