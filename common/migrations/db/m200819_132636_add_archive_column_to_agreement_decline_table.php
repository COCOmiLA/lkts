<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m200819_132636_add_archive_column_to_agreement_decline_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%agreement_decline}}', 'archive', $this->boolean()->null());
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%agreement_decline}}', 'archive');
    }
}
