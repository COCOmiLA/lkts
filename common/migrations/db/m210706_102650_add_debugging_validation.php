<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210706_102650_add_debugging_validation extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%debuggingsoap}}', 'model_validation_debugging_enable', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%debuggingsoap}}', 'model_validation_debugging_enable');
    }
}
