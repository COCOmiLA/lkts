<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210805_045810_add_dictionaries_logging_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%debuggingsoap}}', 'enable_logging_for_dictionary_soap', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%debuggingsoap}}', 'enable_logging_for_dictionary_soap');
    }
}
