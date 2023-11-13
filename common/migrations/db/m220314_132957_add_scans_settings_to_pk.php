<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220314_132957_add_scans_settings_to_pk extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%application_type}}', 'hide_scans_page', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%application_type}}', 'minify_scans_page', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%application_type}}', 'hide_scans_page');
        $this->dropColumn('{{%application_type}}', 'minify_scans_page');
    }
}
