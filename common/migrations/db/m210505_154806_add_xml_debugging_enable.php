<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210505_154806_add_xml_debugging_enable extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%debuggingsoap}}', 'xml_debugging_enable', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%debuggingsoap}}', 'xml_debugging_enable');
    }
}
