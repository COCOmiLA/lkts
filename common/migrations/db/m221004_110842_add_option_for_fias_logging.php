<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m221004_110842_add_option_for_fias_logging extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%debuggingsoap}}', 'enable_logging_for_kladr_soap', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%debuggingsoap}}', 'enable_logging_for_kladr_soap');
    }
}
